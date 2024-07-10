<?php

use Botble\Base\Facades\AdminHelper;
use FriendsOfBotble\EcommerceCustomField\Http\Controllers\CustomFieldController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::prefix('ecommerce-custom-fields')->name('ecommerce-custom-fields.')->group(function () {
        Route::resource('', CustomFieldController::class)->parameters(['' => 'custom-field']);
    });
});
