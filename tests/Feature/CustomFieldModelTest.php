<?php

namespace FriendsOfBotble\EcommerceCustomField\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Models\CustomFieldValue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomFieldModelTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomField(array $attributes = []): CustomField
    {
        return CustomField::query()->create(array_merge([
            'label' => 'Test Field',
            'name' => 'test_field',
            'type' => CustomFieldType::TEXT,
            'status' => BaseStatusEnum::PUBLISHED,
            'display_location' => DisplayLocation::PRODUCT,
        ], $attributes));
    }

    public function test_can_create_custom_field(): void
    {
        $field = $this->createCustomField();

        $this->assertDatabaseHas('ec_custom_fields', [
            'label' => 'Test Field',
            'name' => 'test_field',
        ]);
    }

    public function test_can_create_field_with_each_display_location(): void
    {
        foreach (DisplayLocation::values() as $location) {
            $field = $this->createCustomField([
                'name' => "field_{$location}",
                'display_location' => $location,
            ]);

            $this->assertEquals($location, $field->display_location->getValue());
        }
    }

    public function test_query_scoping_published_by_location(): void
    {
        $this->createCustomField([
            'name' => 'login_field',
            'display_location' => DisplayLocation::LOGIN,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->createCustomField([
            'name' => 'register_field',
            'display_location' => DisplayLocation::REGISTER,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->createCustomField([
            'name' => 'draft_login_field',
            'display_location' => DisplayLocation::LOGIN,
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $loginFields = CustomField::query()
            ->wherePublished()
            ->where('display_location', DisplayLocation::LOGIN)
            ->get();

        $this->assertCount(1, $loginFields);
        $this->assertEquals('login_field', $loginFields->first()->name);
    }

    public function test_formatted_options_accessor_for_select(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::SELECT,
            'options' => [
                [['value' => 'Option A'], ['value' => 'a']],
                [['value' => 'Option B'], ['value' => 'b']],
            ],
        ]);

        $formatted = $field->formatted_options;

        $this->assertCount(2, $formatted);
        $this->assertEquals('Option A', $formatted->get('a'));
        $this->assertEquals('Option B', $formatted->get('b'));
    }

    public function test_get_default_value_for_readonly_text(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::READONLY_TEXT,
            'options' => ['default_value' => 'Hello World'],
        ]);

        $this->assertEquals('Hello World', $field->getDefaultValue());
    }

    public function test_get_default_value_returns_empty_for_non_readonly(): void
    {
        $field = $this->createCustomField(['type' => CustomFieldType::TEXT]);

        $this->assertEquals('', $field->getDefaultValue());
    }

    public function test_get_file_accepted_types_for_file_field(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::FILE,
            'options' => ['accepted_types' => 'pdf,doc,docx'],
        ]);

        $this->assertEquals('pdf,doc,docx', $field->getFileAcceptedTypes());
    }

    public function test_get_file_accepted_types_for_image_with_custom_types(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::IMAGE,
            'options' => ['accepted_types' => 'jpg,png,webp'],
        ]);

        $this->assertEquals('jpg,png,webp', $field->getFileAcceptedTypes());
    }

    public function test_get_file_accepted_types_returns_empty_for_text(): void
    {
        $field = $this->createCustomField(['type' => CustomFieldType::TEXT]);

        $this->assertEquals('', $field->getFileAcceptedTypes());
    }

    public function test_get_max_file_size(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::FILE,
            'options' => ['max_file_size' => 10],
        ]);

        $this->assertEquals(10, $field->getMaxFileSize());
    }

    public function test_get_max_file_size_returns_null_for_text(): void
    {
        $field = $this->createCustomField(['type' => CustomFieldType::TEXT]);

        $this->assertNull($field->getMaxFileSize());
    }

    public function test_get_max_file_size_for_file_with_options(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::FILE,
            'options' => ['max_file_size' => 5],
        ]);

        $this->assertEquals(5, $field->getMaxFileSize());
    }

    public function test_get_max_file_size_returns_null_without_option(): void
    {
        $field = $this->createCustomField([
            'type' => CustomFieldType::FILE,
            'options' => [],
        ]);

        $this->assertNull($field->getMaxFileSize());
    }

    public function test_deleting_field_cascades_to_values(): void
    {
        $field = $this->createCustomField();

        CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => 'test_model',
            'model_id' => 1,
            'value' => 'test',
        ]);

        $this->assertDatabaseHas('ec_custom_field_values', [
            'custom_field_id' => $field->id,
        ]);

        $field->delete();

        $this->assertDatabaseMissing('ec_custom_field_values', [
            'custom_field_id' => $field->id,
        ]);
    }

    public function test_values_relationship(): void
    {
        $field = $this->createCustomField();

        CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => 'test_model',
            'model_id' => 1,
            'value' => 'val1',
        ]);

        CustomFieldValue::query()->create([
            'custom_field_id' => $field->id,
            'model_type' => 'test_model',
            'model_id' => 2,
            'value' => 'val2',
        ]);

        $this->assertCount(2, $field->values);
    }

    public function test_product_ids_cast_as_array(): void
    {
        $field = $this->createCustomField([
            'apply_to' => 'specific',
            'product_ids' => [1, 2, 3],
        ]);

        $field->refresh();

        $this->assertIsArray($field->product_ids);
        $this->assertCount(3, $field->product_ids);
    }

    public function test_type_cast_to_enum(): void
    {
        $field = $this->createCustomField(['type' => CustomFieldType::SELECT]);

        $this->assertInstanceOf(CustomFieldType::class, $field->type);
        $this->assertEquals(CustomFieldType::SELECT, $field->type->getValue());
    }

    public function test_display_location_cast_to_enum(): void
    {
        $field = $this->createCustomField(['display_location' => DisplayLocation::LOGIN]);

        $this->assertInstanceOf(DisplayLocation::class, $field->display_location);
        $this->assertEquals(DisplayLocation::LOGIN, $field->display_location->getValue());
    }
}
