<?php

namespace FriendsOfBotble\EcommerceCustomField\Providers;

use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\FieldOptions\InputFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Forms\Fronts\CheckoutForm;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use FriendsOfBotble\EcommerceCustomField\Contracts\CustomFieldValuable;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, function (?string $html): ?string {
            return $html . $this->renderCustomFields(DisplayLocation::PRODUCT);
        }, 999, 2);

        add_filter('ecommerce_checkout_form_before_payment_form', function (?string $html): ?string {
            return $html . $this->renderCustomFields(DisplayLocation::CHECKOUT);
        }, 999);

        CheckoutForm::extend(function ($form) {
            $this->addMultipartToCheckoutForm($form);
        });

        ProductForm::beforeRendering(function (ProductForm $form) {
            $customFields = CustomField::query()
                ->wherePublished()
                ->where('display_location', DisplayLocation::PRODUCT_FORM)
                ->get();

            if ($customFields->isEmpty()) {
                return $form;
            }

            foreach ($customFields as $customField) {
                $fieldOptions = InputFieldOption::make()
                    ->label($customField->label);

                if ($customField->type == CustomFieldType::SELECT && $customField->options) {
                    $options = $customField->options;

                    if (! is_array($options)) {
                        $options = json_decode($customField->options, true);
                    }

                    $options = collect($options)->mapWithKeys(
                        fn ($option) => [Arr::get($option, '0.value') => Arr::get($option, '1.value')]
                    )->toArray();

                    $fieldOptions = SelectFieldOption::make()
                        ->label($customField->label);

                    $fieldOptions->choices($options);
                }

                if ($customField->placeholder) {
                    if ($customField->type == CustomFieldType::SELECT) {
                        $fieldOptions->emptyValue($customField->placeholder);
                    } else {
                        $fieldOptions->placeholder($customField->placeholder);
                    }
                }

                $fieldOptions->metadata();

                $form->addAfter(
                    'content',
                    $customField->name,
                    $customField->type,
                    $fieldOptions
                );
            }

            return $form;
        });

        add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($screen, $request, $data) {
            if ($data instanceof Product) {
                $customFields = CustomField::query()
                    ->wherePublished()
                    ->where('display_location', DisplayLocation::PRODUCT_FORM)
                    ->get();

                if ($customFields->isEmpty()) {
                    return;
                }

                foreach ($customFields as $customField) {
                    MetaBox::saveMetaBoxData($data, $customField->name, $request->input($customField->name));
                }
            }
        }, 120, 3);

        add_filter('ecommerce_cart_after_item_content', function (?string $html, CartItem $cartItem): ?string {
            $customFieldValues = Arr::get($cartItem->options->extras, 'custom_fields', []);

            if (empty($customFieldValues)) {
                return $html;
            }

            $customFields = CustomField::query()
                ->whereIn('id', array_keys($customFieldValues))
                ->get()
                ->mapWithKeys(
                    fn (CustomField $customField) => [$customField->label => $customFieldValues[$customField->getKey()]]
                );

            return $html . view('plugins/fob-ecommerce-custom-field::cart-item', compact('customFields'))->render();
        }, 999, 2);

        add_action('ecommerce_create_order_from_data', function (array $data, Order $order) {
            // Get both text inputs and file inputs, then merge them preserving field IDs as keys
            $textInputs = request()->input('extras.custom_fields', []);
            $fileInputs = request()->file('extras.custom_fields', []);
            $allCustomFields = $textInputs + $fileInputs; // Use + operator to preserve keys

            $this->saveCustomFields($order, $allCustomFields);
        }, 999, 2);

        add_action('ecommerce_before_processing_payment', function (Collection $products, Request $request) {
            if (empty($orderIds = (array) $request->input('order_id', []))) {
                return;
            }

            $orders = Order::query()
                ->whereIn('id', $orderIds)
                ->get();

            if ($orders->isEmpty()) {
                return;
            }

            foreach ($orders as $order) {
                /**
                 * @var Order $order
                 */
                // Get both text inputs and file inputs, then merge them preserving field IDs as keys
                $textInputs = $request->input('extras.custom_fields', []);
                $fileInputs = $request->file('extras.custom_fields', []);
                $allCustomFields = $textInputs + $fileInputs; // Use + operator to preserve keys

                $this->saveCustomFields($order, $allCustomFields);
            }
        }, 999, 2);

        add_action('ecommerce_after_each_order_product_created', function (OrderProduct $orderProduct) {
            $this->saveCustomFields($orderProduct, Arr::get($orderProduct->options, 'extras.custom_fields', []));
        });

        add_filter('ecommerce_thank_you_customer_info', function (?string $html, Order $order): ?string {
            $customFieldValues = $order->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/fob-ecommerce-custom-field::thank-you', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('ecommerce_admin_order_extra_info', function (?string $html, Order $order): ?string {
            /**
             * @var Order&CustomFieldValuable $order
             */
            $customFieldValues = $order->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/fob-ecommerce-custom-field::order', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('ecommerce_admin_invoice_extra_info', function (?string $html, Order $order): ?string {
            /**
             * @var Order&CustomFieldValuable $order
             */
            $customFieldValues = $order->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/fob-ecommerce-custom-field::invoice', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('invoice_payment_info_filter', function (?string $html, Invoice $invoice): ?string {
            /**
             * @var Order $order
             */
            $order = $invoice->reference;

            if (! $order) {
                return $html;
            }

            $customFieldValues = $order->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/fob-ecommerce-custom-field::invoice', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('ecommerce_order_product_item_extra_info', function (?string $html, OrderProduct $orderProduct): ?string {
            /** @var OrderProduct&CustomFieldValuable $orderProduct */
            $customFieldValues = $orderProduct->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/fob-ecommerce-custom-field::order-product', compact('customFieldValues'))->render();
        }, 999, 2);
    }

    protected function renderCustomFields(string $location): ?string
    {
        $customFields = CustomField::query()
            ->wherePublished()
            ->where('display_location', $location)
            ->get();

        if ($customFields->isEmpty()) {
            return null;
        }

        return view('plugins/fob-ecommerce-custom-field::fields', compact('customFields'))->render();
    }

    /**
     * @param CustomFieldValuable&BaseModel $model
     */
    protected function saveCustomFields(BaseModel $model, array $fields): void
    {
        $fields = $this->processCustomFieldFiles($fields);

        $customFields = CustomField::query()->findMany(array_keys($fields));

        $values = collect($fields)
            ->filter(fn ($value, $key) => $customFields->contains('id', $key))
            ->map(function ($value, $key) use ($model, $customFields) {
                /**
                 * @var CustomFieldValue $customFieldValue
                 */
                $customFieldValue = CustomFieldValue::query()->firstOrNew([
                    'custom_field_id' => $key,
                    'model_type' => $model->getMorphClass(),
                    'model_id' => $model->getKey(),
                ]);

                if (! $customFieldValue->exists) {
                    $customFieldValue->customField()->associate($key);
                    $customFieldValue->model()->associate($model);
                }

                // Handle file uploads for file and image field types
                $customField = $customFields->firstWhere('id', $key);
                if ($customField && in_array($customField->type, [CustomFieldType::FILE, CustomFieldType::IMAGE])) {
                    // If value is already a file path (from previous processing), use it
                    if (is_string($value) && ! empty($value)) {
                        $customFieldValue->value = $value;
                    }
                } else {
                    $customFieldValue->value = $value;
                }

                return $customFieldValue;
            });

        $model->customFieldValues()->saveMany($values);
    }

    protected function processCustomFieldFiles(array $fields): array
    {
        $customFields = CustomField::query()
            ->whereIn('id', array_keys($fields))
            ->whereIn('type', [CustomFieldType::FILE, CustomFieldType::IMAGE])
            ->get();

        if ($customFields->isEmpty()) {
            return $fields;
        }

        $processedFields = $fields;

        // Debug: Log what files are available
        logger()->info('Processing custom field files', [
            'fields' => array_keys($fields),
            'request_files' => request()->allFiles(),
            'extras_files' => request()->file('extras'),
        ]);

        foreach ($customFields as $customField) {
            $fieldId = $customField->getKey();

            if (! isset($fields[$fieldId])) {
                continue;
            }

            // First check if we have a file URL stored in session
            $sessionFileUrl = session("custom_field_file_{$fieldId}");

            if ($sessionFileUrl) {
                $processedFields[$fieldId] = $sessionFileUrl;
                // Clear the session data after using it
                session()->forget("custom_field_file_{$fieldId}");

                logger()->info('Using file from session', [
                    'field_id' => $fieldId,
                    'url' => $sessionFileUrl,
                ]);

                continue;
            }

            // Check if we have a file upload for this field
            $uploadedFiles = request()->file('extras.custom_fields');
            $uploadedFile = $uploadedFiles[$fieldId] ?? null;

            logger()->info('Checking file for field', [
                'field_id' => $fieldId,
                'has_uploaded_files' => ! empty($uploadedFiles),
                'has_specific_file' => ! empty($uploadedFile),
                'file_valid' => $uploadedFile && $uploadedFile->isValid(),
            ]);

            if ($uploadedFile && $uploadedFile->isValid()) {
                // Validate file according to custom field restrictions
                $validationResult = $this->validateCustomFieldFile($uploadedFile, $customField);

                if ($validationResult !== true) {
                    // Validation failed, skip this file
                    logger()->warning('File validation failed', [
                        'field_id' => $fieldId,
                        'error' => $validationResult,
                    ]);
                    unset($processedFields[$fieldId]);

                    continue;
                }

                // Upload the file using RvMedia
                $result = RvMedia::handleUpload($uploadedFile, 0);

                if (! $result['error']) {
                    $processedFields[$fieldId] = $result['data']->url;
                    logger()->info('File uploaded successfully', [
                        'field_id' => $fieldId,
                        'url' => $result['data']->url,
                    ]);
                } else {
                    // If upload fails, remove the field to prevent errors
                    logger()->error('File upload failed', [
                        'field_id' => $fieldId,
                        'error' => $result['message'] ?? 'Unknown error',
                    ]);
                    unset($processedFields[$fieldId]);
                }
            }
        }

        return $processedFields;
    }

    protected function validateCustomFieldFile(UploadedFile $file, CustomField $customField): bool|string
    {
        $rules = ['required', 'file'];

        // Add file type validation
        if ($acceptedTypes = $customField->getFileAcceptedTypes()) {
            $rules[] = 'mimes:' . $acceptedTypes;
        }

        // Add file size validation
        if ($maxSize = $customField->getMaxFileSize()) {
            $rules[] = 'max:' . ($maxSize * 1024); // Convert MB to KB for Laravel validation
        }

        $validator = Validator::make(['file' => $file], ['file' => $rules]);

        if ($validator->fails()) {
            return $validator->getMessageBag()->first();
        }

        return true;
    }

    protected function addMultipartToCheckoutForm($form): void
    {
        // Check if there are any file/image custom fields for checkout
        $hasFileFields = CustomField::query()
            ->wherePublished()
            ->where('display_location', DisplayLocation::CHECKOUT)
            ->whereIn('type', [CustomFieldType::FILE, CustomFieldType::IMAGE])
            ->exists();

        if ($hasFileFields) {
            // Get current form options
            $formOptions = $form->getFormOptions();

            // Add enctype for file uploads
            $formOptions['enctype'] = 'multipart/form-data';

            // Update form options
            $form->setFormOptions($formOptions);

            logger()->info('Added multipart/form-data to checkout form');
        }
    }
}
