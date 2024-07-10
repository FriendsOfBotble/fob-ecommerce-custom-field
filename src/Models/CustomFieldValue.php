<?php

namespace FriendsOfBotble\EcommerceCustomField\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends BaseModel
{
    protected $table = 'ec_custom_field_values';

    protected $fillable = [
        'custom_field_id',
        'model_type',
        'model_id',
        'value',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }
}
