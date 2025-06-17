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
        return [
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'alpha_dash', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'type' => [Rule::in(CustomFieldType::values())],
            'display_location' => ['required', Rule::in(DisplayLocation::values())],
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
            'status' => [Rule::in(BaseStatusEnum::values())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type');

        // Handle file options for file and image types
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
    }
}
