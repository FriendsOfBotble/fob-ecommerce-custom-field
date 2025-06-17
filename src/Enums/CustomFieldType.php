<?php

namespace FriendsOfBotble\EcommerceCustomField\Enums;

use Botble\Base\Supports\Enum;

class CustomFieldType extends Enum
{
    public const TEXT = 'text';

    public const NUMBER = 'number';

    public const TIME = 'time';

    public const DATE = 'date';

    public const DATETIME = 'datetime';

    public const TEXTAREA = 'textarea';

    public const SELECT = 'select';

    public const FILE = 'file';

    public const IMAGE = 'image';

    protected static $langPath = 'plugins/fob-ecommerce-custom-field::custom-field.types';
}
