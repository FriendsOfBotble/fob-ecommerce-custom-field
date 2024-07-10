<?php

namespace FriendsOfBotble\EcommerceCustomField\Forms;

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
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Http\Requests\CustomFieldRequest;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;

class CustomFieldForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(CustomField::class)
            ->setValidatorClass(CustomFieldRequest::class)
            ->withCustomFields()
            ->add(
                'type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-custom-field::custom-field.type'))
                    ->choices(CustomFieldType::labels())
                    ->required()
            )
            ->add(
                'label',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce-custom-field::custom-field.label'))
                    ->helperText(trans('plugins/ecommerce-custom-field::custom-field.label_helper'))
                    ->required()
            )
            ->add(
                'name',
                TextField::class,
                NameFieldOption::make()
                    ->required()
                    ->helperText(trans('plugins/ecommerce-custom-field::custom-field.name_helper'))
            )
            ->add(
                'placeholder',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce-custom-field::custom-field.placeholder'))
                    ->helperText(trans('plugins/ecommerce-custom-field::custom-field.placeholder_helper'))
                    ->maxLength(255),
            )
            ->add(
                'display_location',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('plugins/ecommerce-custom-field::custom-field.display_location'))
                    ->choices(DisplayLocation::labels())
                    ->required()
                    ->helperText(trans('plugins/ecommerce-custom-field::custom-field.display_location_helper'))
            )
            ->add(
                'options',
                RepeaterField::class,
                RepeaterFieldOption::make()
                    ->collapsible('type', CustomFieldType::SELECT, $this->getModel()->type ?: CustomFieldType::TEXT)
                    ->label(trans('plugins/ecommerce-custom-field::custom-field.options'))
                    ->fields([
                        [
                            'type' => 'text',
                            'label' => trans('plugins/ecommerce-custom-field::custom-field.value'),
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
                            'label' => trans('plugins/ecommerce-custom-field::custom-field.label'),
                            'attributes' => [
                                'name' => 'label',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ],
                        ],
                    ]),
            )
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
