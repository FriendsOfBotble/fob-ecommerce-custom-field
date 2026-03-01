<?php

namespace FriendsOfBotble\EcommerceCustomField\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomFieldValueMorphTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomField(array $attributes = []): CustomField
    {
        return CustomField::query()->create(array_merge([
            'label' => 'Test Field',
            'name' => 'test_field',
            'type' => CustomFieldType::TEXT,
            'status' => BaseStatusEnum::PUBLISHED,
            'display_location' => DisplayLocation::CHECKOUT,
        ], $attributes));
    }

    public function test_save_value_against_order(): void
    {
        $field = $this->createCustomField();

        $order = Order::query()->create([
            'amount' => 100,
            'sub_total' => 100,
            'currency_id' => 1,
            'user_id' => 0,
        ]);

        $value = CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => $order->getMorphClass(),
            'model_id' => $order->getKey(),
            'value' => 'Order custom value',
        ]);

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->id,
            'model_type' => $order->getMorphClass(),
            'model_id' => $order->getKey(),
            'value' => 'Order custom value',
        ]);
    }

    public function test_save_value_against_customer(): void
    {
        $field = $this->createCustomField([
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
        ]);

        $value = CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Customer custom value',
        ]);

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->id,
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Customer custom value',
        ]);
    }

    public function test_customer_custom_field_values_relationship(): void
    {
        $field1 = $this->createCustomField([
            'name' => 'field_1',
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $field2 = $this->createCustomField([
            'name' => 'field_2',
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
        ]);

        CustomFieldValue::query()->create([
            'custom_field_id' => $field1->id,
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Value 1',
        ]);

        CustomFieldValue::query()->create([
            'custom_field_id' => $field2->id,
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Value 2',
        ]);

        $customer->load('customFieldValues');

        $this->assertCount(2, $customer->customFieldValues);
    }

    public function test_order_custom_field_values_relationship(): void
    {
        $field = $this->createCustomField();

        $order = Order::query()->create([
            'amount' => 50,
            'sub_total' => 50,
            'currency_id' => 1,
            'user_id' => 0,
        ]);

        CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => $order->getMorphClass(),
            'model_id' => $order->getKey(),
            'value' => 'Order value',
        ]);

        $order->load('customFieldValues');

        $this->assertCount(1, $order->customFieldValues);
        $this->assertEquals('Order value', $order->customFieldValues->first()->value);
    }

    public function test_first_or_new_upsert_pattern(): void
    {
        $field = $this->createCustomField([
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
        ]);

        $value = CustomFieldValue::query()->firstOrNew([
            'custom_field_id' => $field->id,
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
        ]);
        $value->value = 'Initial value';
        $value->save();

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->id,
            'model_id' => $customer->getKey(),
            'value' => 'Initial value',
        ]);

        $updated = CustomFieldValue::query()->firstOrNew([
            'custom_field_id' => $field->id,
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
        ]);
        $updated->value = 'Updated value';
        $updated->save();

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->id,
            'model_id' => $customer->getKey(),
            'value' => 'Updated value',
        ]);

        $this->assertEquals(
            1,
            CustomFieldValue::query()
                ->where('custom_field_id', $field->id)
                ->where('model_id', $customer->getKey())
                ->count()
        );
    }

    public function test_custom_field_belongs_to_relationship(): void
    {
        $field = $this->createCustomField();

        $value = CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => 'test_model',
            'model_id' => 1,
            'value' => 'test',
        ]);

        $this->assertEquals($field->id, $value->customField->id);
        $this->assertEquals('Test Field', $value->customField->label);
    }
}
