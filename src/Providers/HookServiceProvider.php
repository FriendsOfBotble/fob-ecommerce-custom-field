<?php

namespace FriendsOfBotble\EcommerceCustomField\Providers;

use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use FriendsOfBotble\EcommerceCustomField\Contracts\CustomFieldValuable;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

            return $html . view('plugins/ecommerce-custom-field::cart-item', compact('customFields'))->render();
        }, 999, 2);

        add_action('ecommerce_create_order_from_data', function (array $data, Order $order) {
            $this->saveCustomFields($order, request()->input('extras.custom_fields', []));
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
                $this->saveCustomFields($order, $request->input('extras.custom_fields', []));
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

            return $html . view('plugins/ecommerce-custom-field::thank-you', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('ecommerce_admin_order_extra_info', function (?string $html, Order $order): ?string {
            /** @var Order&CustomFieldValuable $order */
            $customFieldValues = $order->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/ecommerce-custom-field::order', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('ecommerce_admin_invoice_extra_info', function (?string $html, Order $order): ?string {
            /** @var Order&CustomFieldValuable $order */
            $customFieldValues = $order->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/ecommerce-custom-field::invoice', compact('customFieldValues'))->render();
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

            return $html . view('plugins/ecommerce-custom-field::invoice', compact('customFieldValues'))->render();
        }, 999, 2);

        add_filter('ecommerce_order_product_item_extra_info', function (?string $html, OrderProduct $orderProduct): ?string {
            /** @var OrderProduct&CustomFieldValuable $orderProduct */
            $customFieldValues = $orderProduct->customFieldValues;

            if ($customFieldValues->isEmpty()) {
                return $html;
            }

            return $html . view('plugins/ecommerce-custom-field::order-product', compact('customFieldValues'))->render();
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

        return view('plugins/ecommerce-custom-field::fields', compact('customFields'))->render();
    }

    /**
     * @param CustomFieldValuable&BaseModel $model
     */
    protected function saveCustomFields(BaseModel $model, array $fields): void
    {
        $customFields = CustomField::query()->findMany(array_keys($fields));

        $values = collect($fields)
            ->filter(fn ($value, $key) => $customFields->contains('id', $key))
            ->map(function ($value, $key) use ($model) {
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

                $customFieldValue->value = $value;

                return $customFieldValue;
            });

        $model->customFieldValues()->saveMany($values);
    }
}
