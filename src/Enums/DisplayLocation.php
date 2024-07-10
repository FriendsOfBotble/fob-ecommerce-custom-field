<?php

namespace FriendsOfBotble\EcommerceCustomField\Enums;

use Botble\Base\Supports\Enum;

class DisplayLocation extends Enum
{
    public const PRODUCT = 'product';

    public const CHECKOUT = 'checkout';

    protected static $langPath = 'plugins/ecommerce-custom-field::custom-field.display_locations';
}
