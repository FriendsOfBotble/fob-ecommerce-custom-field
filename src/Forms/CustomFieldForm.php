<?php

namespace FriendsOfBotble\EcommerceCustomField\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\RepeaterFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\RepeaterField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Models\Product;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Http\Requests\CustomFieldRequest;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use Illuminate\Support\Arr;

class CustomFieldForm extends FormAbstract
{
    public function setup(): void
    {
        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/flash-sale.js')
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/ecommerce.css');

        $model = $this->getModel();
        $isExistingModel = $model instanceof CustomField && $model->getKey();
        $modelType = $isExistingModel ? $model->type : null;
        $products = collect();

        if ($isExistingModel && $model->apply_to === 'specific' && ! empty($model->product_ids)) {
            $products = Product::query()
                ->whereIn('id', $model->product_ids)
                ->get();
        }

        $this
            ->model(CustomField::class)
            ->setValidatorClass(CustomFieldRequest::class)
            ->withCustomFields()
            ->add(
                'type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.type'))
                    ->choices(CustomFieldType::labels())
                    ->required()
            )
            ->add(
                'label',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.label'))
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.label_helper'))
                    ->required()
            )
            ->add(
                'name',
                TextField::class,
                NameFieldOption::make()
                    ->required()
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.name_helper'))
            )
            ->add(
                'placeholder',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.placeholder'))
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.placeholder_helper'))
                    ->maxLength(255),
            )
            ->add(
                'display_location',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.display_location'))
                    ->choices(DisplayLocation::labels())
                    ->required()
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.display_location_helper'))
            )
            ->add(
                'apply_to',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.apply_to'))
                    ->choices([
                        'all' => trans('plugins/fob-ecommerce-custom-field::custom-field.apply_to_all'),
                        'specific' => trans('plugins/fob-ecommerce-custom-field::custom-field.apply_to_specific'),
                    ])
                    ->defaultValue(old('apply_to', $isExistingModel ? ($model->apply_to ?: 'all') : 'all'))
                    ->collapsible('display_location', DisplayLocation::PRODUCT, old('display_location', $isExistingModel ? $model->display_location : null))
            )
            ->add(
                'options',
                RepeaterField::class,
                RepeaterFieldOption::make()
                    ->collapsible('type', CustomFieldType::SELECT, old('type', $modelType) ?: CustomFieldType::TEXT)
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.options'))
                    ->fields([
                        [
                            'type' => 'text',
                            'label' => trans('plugins/fob-ecommerce-custom-field::custom-field.value'),
                            'attributes' => [
                                'name' => 'value',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ],
                        ],
                        [
                            'type' => 'text',
                            'label' => trans('plugins/fob-ecommerce-custom-field::custom-field.label'),
                            'attributes' => [
                                'name' => 'label',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ],
                        ],
                    ])
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.options_helper')),
            )
            ->add(
                'default_value',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.default_value'))
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.default_value_helper'))
                    ->value(old('default_value', $this->getOptionValue('default_value')))
                    ->collapsible('type', CustomFieldType::READONLY_TEXT, old('type', $modelType) ?: CustomFieldType::TEXT)
            )
            ->add(
                'file_accepted_types',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.file_accepted_types'))
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.file_accepted_types_helper'))
                    ->placeholder('jpg,jpeg,png,pdf,doc,docx')
                    ->value(old('file_accepted_types', $this->getFileOptionValue('accepted_types')))
                    ->collapsible('type', [CustomFieldType::FILE, CustomFieldType::IMAGE], old('type', $modelType) ?: CustomFieldType::TEXT)
            )
            ->add(
                'file_max_size',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.file_max_size'))
                    ->helperText(trans('plugins/fob-ecommerce-custom-field::custom-field.file_max_size_helper'))
                    ->placeholder('2')
                    ->value(old('file_max_size', $this->getFileOptionValue('max_file_size')))
                    ->collapsible('type', [CustomFieldType::FILE, CustomFieldType::IMAGE], old('type', $modelType) ?: CustomFieldType::TEXT)
                    ->attributes([
                        'type' => 'number',
                        'min' => '1',
                        'max' => '100',
                    ])
            )
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->addMetaBoxes([
                'product_selector' => [
                    'title' => trans('plugins/fob-ecommerce-custom-field::custom-field.select_products'),
                    'content' => view('plugins/fob-ecommerce-custom-field::product-selector', [
                        'customField' => $model,
                        'products' => $products,
                    ]),
                    'priority' => 1,
                    'attributes' => [
                        'id' => 'product-selector-box',
                    ],
                ],
            ])
            ->setBreakFieldPoint('status');
    }

    protected function getOptionValue(string $key): string
    {
        return $this->getFileOptionValue($key);
    }

    protected function getFileOptionValue(string $key): string
    {
        $model = $this->getModel();
        if (! $model instanceof CustomField || ! $model->exists) {
            return '';
        }

        $options = $model->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        return (string) Arr::get($options, $key, '');
    }
}
