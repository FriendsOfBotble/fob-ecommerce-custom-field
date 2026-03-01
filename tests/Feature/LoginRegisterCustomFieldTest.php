<?php

namespace FriendsOfBotble\EcommerceCustomField\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue;
use FriendsOfBotble\EcommerceCustomField\Providers\HookServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginRegisterCustomFieldTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomField(array $attributes = []): CustomField
    {
        return CustomField::query()->create(array_merge([
            'label' => 'Test Field',
            'name' => 'test_field',
            'type' => CustomFieldType::TEXT,
            'status' => BaseStatusEnum::PUBLISHED,
            'display_location' => DisplayLocation::REGISTER,
        ], $attributes));
    }

    protected function makeHookServiceProvider(): HookServiceProvider
    {
        return new HookServiceProvider(app());
    }

    public function test_custom_field_values_saved_on_registration_event(): void
    {
        $field = $this->createCustomField([
            'label' => 'Company Name',
            'name' => 'company_name',
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->app['request']->merge([
            'extras' => [
                'custom_fields' => [
                    $field->getKey() => 'Acme Corp',
                ],
            ],
        ]);

        event(new Registered($customer));

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->getKey(),
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Acme Corp',
        ]);
    }

    public function test_custom_field_values_saved_on_login_filter(): void
    {
        $field = $this->createCustomField([
            'label' => 'Department',
            'name' => 'department',
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Existing Customer',
            'email' => 'existing@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->app['request']->merge([
            'extras' => [
                'custom_fields' => [
                    $field->getKey() => 'Engineering',
                ],
            ],
        ]);

        apply_filters('customer_login_response', null, $customer, request());

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->getKey(),
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Engineering',
        ]);
    }

    public function test_login_filter_updates_existing_value(): void
    {
        $field = $this->createCustomField([
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
        ]);

        CustomFieldValue::query()->create([
            'custom_field_id' => $field->getKey(),
            'model_type' => $customer->getMorphClass(),
            'model_id' => $customer->getKey(),
            'value' => 'Old Value',
        ]);

        $this->app['request']->merge([
            'extras' => [
                'custom_fields' => [
                    $field->getKey() => 'New Value',
                ],
            ],
        ]);

        apply_filters('customer_login_response', null, $customer, request());

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->getKey(),
            'model_id' => $customer->getKey(),
            'value' => 'New Value',
        ]);

        $this->assertEquals(
            1,
            CustomFieldValue::query()
                ->where('custom_field_id', $field->getKey())
                ->where('model_id', $customer->getKey())
                ->count()
        );
    }

    public function test_registration_event_ignores_non_customer_users(): void
    {
        $this->createCustomField([
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $nonCustomerUser = new User();
        $nonCustomerUser->forceFill([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
        ]);
        $nonCustomerUser->save();

        $this->app['request']->merge([
            'extras' => ['custom_fields' => [1 => 'value']],
        ]);

        event(new Registered($nonCustomerUser));

        $this->assertDatabaseMissing('ec_custom_field_values', [
            'model_id' => $nonCustomerUser->getKey(),
        ]);
    }

    public function test_no_values_saved_when_request_has_no_fields(): void
    {
        $this->createCustomField([
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $this->assertEquals(0, CustomFieldValue::query()->count());
    }

    public function test_multiple_fields_saved_on_registration(): void
    {
        $field1 = $this->createCustomField([
            'label' => 'Company',
            'name' => 'company',
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $field2 = $this->createCustomField([
            'label' => 'Role',
            'name' => 'role',
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->app['request']->merge([
            'extras' => [
                'custom_fields' => [
                    $field1->getKey() => 'Acme Corp',
                    $field2->getKey() => 'Developer',
                ],
            ],
        ]);

        event(new Registered($customer));

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field1->getKey(),
            'value' => 'Acme Corp',
        ]);

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field2->getKey(),
            'value' => 'Developer',
        ]);
    }

    public function test_render_custom_fields_returns_null_when_no_login_fields(): void
    {
        $hookProvider = $this->makeHookServiceProvider();

        $reflection = new \ReflectionMethod($hookProvider, 'renderCustomFields');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($hookProvider, DisplayLocation::LOGIN);

        $this->assertNull($result);
    }

    public function test_render_custom_fields_returns_html_when_login_fields_exist(): void
    {
        $this->createCustomField([
            'label' => 'Access Code',
            'name' => 'access_code',
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $hookProvider = $this->makeHookServiceProvider();

        $reflection = new \ReflectionMethod($hookProvider, 'renderCustomFields');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($hookProvider, DisplayLocation::LOGIN);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Access Code', $result);
    }

    public function test_render_custom_fields_returns_null_when_no_register_fields(): void
    {
        $hookProvider = $this->makeHookServiceProvider();

        $reflection = new \ReflectionMethod($hookProvider, 'renderCustomFields');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($hookProvider, DisplayLocation::REGISTER);

        $this->assertNull($result);
    }

    public function test_render_custom_fields_returns_html_when_register_fields_exist(): void
    {
        $this->createCustomField([
            'label' => 'Organization',
            'name' => 'organization',
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $hookProvider = $this->makeHookServiceProvider();

        $reflection = new \ReflectionMethod($hookProvider, 'renderCustomFields');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($hookProvider, DisplayLocation::REGISTER);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Organization', $result);
    }

    public function test_add_custom_field_validation_rules_for_login(): void
    {
        $field = $this->createCustomField([
            'label' => 'Token',
            'name' => 'token',
            'type' => CustomFieldType::TEXT,
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $hookProvider = $this->makeHookServiceProvider();

        $reflection = new \ReflectionMethod($hookProvider, 'addCustomFieldValidationRules');
        $reflection->setAccessible(true);

        $rules = $reflection->invoke($hookProvider, [], DisplayLocation::LOGIN);

        $this->assertArrayHasKey("extras.custom_fields.{$field->getKey()}", $rules);
        $this->assertContains('string', $rules["extras.custom_fields.{$field->getKey()}"]);
    }

    public function test_add_custom_field_validation_rules_for_file_login(): void
    {
        $field = $this->createCustomField([
            'label' => 'Document',
            'name' => 'document',
            'type' => CustomFieldType::FILE,
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $hookProvider = $this->makeHookServiceProvider();

        $reflection = new \ReflectionMethod($hookProvider, 'addCustomFieldValidationRules');
        $reflection->setAccessible(true);

        $rules = $reflection->invoke($hookProvider, [], DisplayLocation::LOGIN);

        $fieldRules = $rules["extras.custom_fields.{$field->getKey()}"];
        $this->assertContains('file', $fieldRules);
        $this->assertNotContains('string', $fieldRules);
    }
}
