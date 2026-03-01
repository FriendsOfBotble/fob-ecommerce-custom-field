<?php

namespace FriendsOfBotble\EcommerceCustomField\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class CustomFieldControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    protected function createAdminUser(): User
    {
        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);

        return $user;
    }

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

    public function test_index_page_is_accessible(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->get(route('ecommerce-custom-fields.index'));

        $response->assertOk();
    }

    public function test_edit_page_is_accessible(): void
    {
        $this->actingAs($this->admin, 'web');

        $field = $this->createCustomField();

        $response = $this->get(route('ecommerce-custom-fields.edit', $field->getKey()));

        $response->assertOk();
    }

    public function test_edit_page_for_login_field(): void
    {
        $this->actingAs($this->admin, 'web');

        $field = $this->createCustomField([
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $response = $this->get(route('ecommerce-custom-fields.edit', $field->getKey()));

        $response->assertOk();
    }

    public function test_edit_page_for_register_field(): void
    {
        $this->actingAs($this->admin, 'web');

        $field = $this->createCustomField([
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $response = $this->get(route('ecommerce-custom-fields.edit', $field->getKey()));

        $response->assertOk();
    }

    public function test_update_route_is_accessible(): void
    {
        $this->actingAs($this->admin, 'web');

        $field = $this->createCustomField([
            'label' => 'Old Label',
            'name' => 'old_name',
        ]);

        $response = $this->put(route('ecommerce-custom-fields.update', $field->getKey()), [
            'label' => 'New Label',
            'name' => 'new_name',
            'type' => CustomFieldType::TEXT,
            'display_location' => DisplayLocation::LOGIN,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_can_delete_field(): void
    {
        $this->actingAs($this->admin, 'web');

        $field = $this->createCustomField();
        $fieldId = $field->getKey();

        $response = $this->delete(route('ecommerce-custom-fields.destroy', $fieldId));

        $this->assertDatabaseMissing('ec_custom_fields', ['id' => $fieldId]);
    }

    public function test_store_validation_fails_without_label(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->post(route('ecommerce-custom-fields.store'), [
            'label' => '',
            'name' => 'test',
            'type' => CustomFieldType::TEXT,
            'display_location' => DisplayLocation::CHECKOUT,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $response->assertSessionHasErrors('label');
    }

    public function test_store_validation_fails_without_name(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->post(route('ecommerce-custom-fields.store'), [
            'label' => 'Test',
            'name' => '',
            'type' => CustomFieldType::TEXT,
            'display_location' => DisplayLocation::CHECKOUT,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_validation_fails_with_invalid_display_location(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->post(route('ecommerce-custom-fields.store'), [
            'label' => 'Test',
            'name' => 'test',
            'type' => CustomFieldType::TEXT,
            'display_location' => 'invalid_location',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $response->assertSessionHasErrors('display_location');
    }

    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->get(route('ecommerce-custom-fields.index'));

        $response->assertRedirect();
    }

    public function test_unauthenticated_user_cannot_access_edit(): void
    {
        $field = $this->createCustomField();

        $response = $this->get(route('ecommerce-custom-fields.edit', $field->getKey()));

        $response->assertRedirect();
    }
}
