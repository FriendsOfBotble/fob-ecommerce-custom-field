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
        'apply_to',
        'product_ids',
    ];

    protected $casts = [
        'type' => CustomFieldType::class,
        'status' => BaseStatusEnum::class,
        'options' => 'array',
        'display_location' => DisplayLocation::class,
        'product_ids' => 'array',
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

    public function getFileAcceptedTypes(): string
    {
        if (! in_array($this->type, [CustomFieldType::FILE, CustomFieldType::IMAGE])) {
            return '';
        }

        $options = $this->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        $acceptedTypes = Arr::get($options, 'accepted_types', '');

        if ($this->type === CustomFieldType::IMAGE && empty($acceptedTypes)) {
            return 'jpg,jpeg,png,gif,webp,bmp';
        }

        return $acceptedTypes ?: '';
    }

    public function getMaxFileSize(): ?int
    {
        if (! in_array($this->type, [CustomFieldType::FILE, CustomFieldType::IMAGE])) {
            return null;
        }

        $options = $this->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        return Arr::get($options, 'max_file_size') ?: null;
    }

    public function getDefaultValue(): string
    {
        if ($this->type != CustomFieldType::READONLY_TEXT) {
            return '';
        }

        $options = $this->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        return (string) Arr::get($options, 'default_value', '');
    }

    public function getFileAcceptAttribute(): string
    {
        if ($this->type === CustomFieldType::IMAGE) {
            $acceptedTypes = $this->getFileAcceptedTypes();
            if ($acceptedTypes) {
                $extensions = explode(',', $acceptedTypes);

                return implode(',', array_map(fn ($ext) => '.' . trim($ext), $extensions));
            }

            return 'image/*';
        }

        if ($this->type === CustomFieldType::FILE) {
            $acceptedTypes = $this->getFileAcceptedTypes();
            if ($acceptedTypes) {
                $extensions = explode(',', $acceptedTypes);

                return implode(',', array_map(fn ($ext) => '.' . trim($ext), $extensions));
            }
        }

        return '';
    }
}
