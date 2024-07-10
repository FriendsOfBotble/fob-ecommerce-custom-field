<?php

namespace FriendsOfBotble\EcommerceCustomField\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Events\RouteMatched;

class EcommerceCustomFieldServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->setNamespace('plugins/ecommerce-custom-field')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadRoutes();

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-ecommerce-custom-field',
                'priority' => 999,
                'parent_id' => 'cms-plugins-ecommerce',
                'name' => 'plugins/ecommerce-custom-field::custom-field.dashboard_menu_label',
                'icon' => 'fas fa-plus',
                'url' => route('ecommerce-custom-fields.index'),
                'permissions' => ['ecommerce-custom-fields.index'],
            ]);
        });

        Order::resolveRelationUsing('customFieldValues', function (Order $order) {
            return $order->morphMany(CustomFieldValue::class, 'model');
        });

        OrderProduct::resolveRelationUsing('customFieldValues', function (OrderProduct $orderProduct) {
            return $orderProduct->morphMany(CustomFieldValue::class, 'model');
        });

        if (defined('LANGUAGE_MODULE_SCREEN_NAME') && defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            LanguageAdvancedManager::registerModule(CustomField::class, [
                'label',
                'placeholder',
                'options',
            ]);
        }

        $this->app->booted(fn (Application $app) => $app->register(HookServiceProvider::class));
    }
}
