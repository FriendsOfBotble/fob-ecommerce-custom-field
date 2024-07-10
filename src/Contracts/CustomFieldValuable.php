<?php

namespace FriendsOfBotble\EcommerceCustomField\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue[] $customFieldValues
 */
interface CustomFieldValuable
{
    public function customFieldValues(): MorphMany;
}
