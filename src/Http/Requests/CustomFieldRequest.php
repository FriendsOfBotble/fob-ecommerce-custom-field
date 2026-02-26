<?php

namespace FriendsOfBotble\EcommerceCustomField\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use Illuminate\Validation\Rule;

class CustomFieldRequest extends Request
{
    public function rules(): array
    {
        $isProduct = $this->input('display_location') === DisplayLocation::PRODUCT;

        return [
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'alpha_dash', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'type' => [Rule::in(CustomFieldType::values())],
            'display_location' => ['required', Rule::in(DisplayLocation::values())],
            'apply_to' => Rule::when($isProduct, ['required', Rule::in(['all', 'specific'])], ['nullable']),
            'product_ids' => Rule::when($isProduct && $this->input('apply_to') === 'specific', ['required', 'array', 'min:1'], ['nullable']),
            'options' => Rule::when($this->input('type') === CustomFieldType::SELECT, ['required', 'array'], ['nullable']),
            'file_accepted_types' => Rule::when(
                in_array($this->input('type'), [CustomFieldType::FILE, CustomFieldType::IMAGE]),
                ['nullable', 'string', 'max:255'],
                ['nullable']
            ),
            'file_max_size' => Rule::when(
                in_array($this->input('type'), [CustomFieldType::FILE, CustomFieldType::IMAGE]),
                ['nullable', 'integer', 'min:1', 'max:100'],
                ['nullable']
            ),
            'default_value' => Rule::when(
                $this->input('type') === CustomFieldType::READONLY_TEXT,
                ['required', 'string', 'max:1000'],
                ['nullable']
            ),
            'status' => [Rule::in(BaseStatusEnum::values())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type');

        if ($type === CustomFieldType::READONLY_TEXT) {
            $this->merge(['options' => ['default_value' => $this->input('default_value', '')]]);
        }

        if (in_array($type, [CustomFieldType::FILE, CustomFieldType::IMAGE])) {
            $fileOptions = [];

            if ($acceptedTypes = $this->input('file_accepted_types')) {
                $fileOptions['accepted_types'] = $acceptedTypes;
            }

            if ($maxSize = $this->input('file_max_size')) {
                $fileOptions['max_file_size'] = (int) $maxSize;
            }

            $this->merge(['options' => $fileOptions]);
        }

        if ($this->input('display_location') !== DisplayLocation::PRODUCT) {
            $this->merge(['apply_to' => 'all', 'product_ids' => null]);
        } elseif ($this->input('apply_to') !== 'specific') {
            $this->merge(['product_ids' => null]);
        } else {
            $productIds = $this->input('product_ids');

            if (is_string($productIds)) {
                $productIds = array_values(array_filter(explode(',', $productIds)));
            }

            $this->merge(['product_ids' => $productIds ?: null]);
        }
    }
}
