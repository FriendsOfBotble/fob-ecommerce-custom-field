<?php

namespace FriendsOfBotble\EcommerceCustomField\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class CustomField extends BaseModel
{
    protected $table = 'ec_custom_fields';

    protected $fillable = [
        'label',
        'name',
        'placeholder',
        'type',
        'status',
        'options',
        'display_location',
    ];

    protected $casts = [
        'type' => CustomFieldType::class,
        'status' => BaseStatusEnum::class,
        'options' => 'array',
        'display_location' => DisplayLocation::class,
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $model) {
            $model->values()->delete();
        });
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'custom_field_id');
    }

    protected function formattedOptions(): Attribute
    {
        return Attribute::get(
            fn () => collect($this->options)->mapWithKeys(
                fn ($option) => [Arr::get($option, '1.value') => Arr::get($option, '0.value')]
            ),
        );
    }
}
