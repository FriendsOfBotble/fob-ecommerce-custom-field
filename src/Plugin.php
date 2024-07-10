<?php

namespace FriendsOfBotble\EcommerceCustomField;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('ec_custom_field_values');
        Schema::dropIfExists('ec_custom_fields');
    }
}
