<?php

namespace FriendsOfBotble\EcommerceCustomField\Enums;

use Botble\Base\Supports\Enum;

class DisplayLocation extends Enum
{
    public const PRODUCT = 'product';

    public const CHECKOUT = 'checkout';

    public const PRODUCT_FORM = 'product_form';

    protected static $langPath = 'plugins/fob-ecommerce-custom-field::custom-field.display_locations';
}
