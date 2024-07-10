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
            'status' => [Rule::in(BaseStatusEnum::values())],
        ];
    }
}
