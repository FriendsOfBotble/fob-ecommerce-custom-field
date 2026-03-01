<?php

namespace FriendsOfBotble\EcommerceCustomField\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;

class DisplayLocationEnumTest extends BaseTestCase
{
    public function test_all_enum_values_exist(): void
    {
        $this->assertEquals('product', DisplayLocation::PRODUCT);
        $this->assertEquals('checkout', DisplayLocation::CHECKOUT);
        $this->assertEquals('product_form', DisplayLocation::PRODUCT_FORM);
        $this->assertEquals('login', DisplayLocation::LOGIN);
        $this->assertEquals('register', DisplayLocation::REGISTER);
    }

    public function test_values_returns_all_five(): void
    {
        $values = DisplayLocation::values();

        $this->assertCount(5, $values);
        $this->assertArrayHasKey('PRODUCT', $values);
        $this->assertArrayHasKey('CHECKOUT', $values);
        $this->assertArrayHasKey('PRODUCT_FORM', $values);
        $this->assertArrayHasKey('LOGIN', $values);
        $this->assertArrayHasKey('REGISTER', $values);
    }

    public function test_labels_returns_all_five(): void
    {
        $labels = DisplayLocation::labels();

        $this->assertCount(5, $labels);
        $this->assertArrayHasKey('product', $labels);
        $this->assertArrayHasKey('checkout', $labels);
        $this->assertArrayHasKey('product_form', $labels);
        $this->assertArrayHasKey('login', $labels);
        $this->assertArrayHasKey('register', $labels);
    }

    public function test_labels_are_strings(): void
    {
        $labels = DisplayLocation::labels();

        foreach ($labels as $key => $label) {
            $this->assertIsString($label, "Label for '{$key}' should be a string");
        }
    }
}
