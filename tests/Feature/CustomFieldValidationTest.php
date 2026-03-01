<?php

namespace FriendsOfBotble\EcommerceCustomField\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use FriendsOfBotble\EcommerceCustomField\Enums\CustomFieldType;
use FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation;
use FriendsOfBotble\EcommerceCustomField\Http\Requests\CustomFieldRequest;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class CustomFieldValidationTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Company Name',
            'name' => 'company_name',
            'placeholder' => 'Enter your company',
            'type' => CustomFieldType::TEXT,
            'display_location' => DisplayLocation::CHECKOUT,
            'status' => BaseStatusEnum::PUBLISHED,
        ], $overrides);
    }

    protected function getRules(array $data = []): array
    {
        $request = CustomFieldRequest::create('/', 'POST', $data ?: $this->validData());
        $request->setContainer(app());

        return $request->rules();
    }

    public function test_valid_text_field_passes(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make($this->validData(), $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_label_is_required(): void
    {
        $data = $this->validData(['label' => '']);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('label', $validator->errors()->toArray());
    }

    public function test_name_is_required(): void
    {
        $data = $this->validData(['name' => '']);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_name_must_be_alpha_dash(): void
    {
        $data = $this->validData(['name' => 'invalid name with spaces']);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_display_location_is_required(): void
    {
        $data = $this->validData(['display_location' => '']);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('display_location', $validator->errors()->toArray());
    }

    public function test_invalid_display_location_fails(): void
    {
        $data = $this->validData(['display_location' => 'nonexistent']);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
    }

    public function test_login_display_location_passes(): void
    {
        $data = $this->validData(['display_location' => DisplayLocation::LOGIN]);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_register_display_location_passes(): void
    {
        $data = $this->validData(['display_location' => DisplayLocation::REGISTER]);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_select_type_requires_options(): void
    {
        $data = $this->validData([
            'type' => CustomFieldType::SELECT,
            'options' => null,
        ]);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
    }

    public function test_select_type_with_options_passes(): void
    {
        $data = $this->validData([
            'type' => CustomFieldType::SELECT,
            'options' => [
                [['value' => 'Option A'], ['value' => 'a']],
            ],
        ]);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_readonly_text_requires_default_value(): void
    {
        $data = $this->validData([
            'type' => CustomFieldType::READONLY_TEXT,
            'default_value' => '',
        ]);
        $rules = $this->getRules($data);
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
    }

    public function test_validation_rules_injected_for_register(): void
    {
        $field = CustomField::query()->create([
            'label' => 'Company',
            'name' => 'company',
            'type' => CustomFieldType::TEXT,
            'status' => BaseStatusEnum::PUBLISHED,
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $rules = apply_filters('ecommerce_customer_registration_form_validation_rules', []);

        $this->assertArrayHasKey("extras.custom_fields.{$field->getKey()}", $rules);

        $fieldRules = $rules["extras.custom_fields.{$field->getKey()}"];
        $this->assertContains('nullable', $fieldRules);
        $this->assertContains('string', $fieldRules);
    }

    public function test_validation_rules_injected_for_login(): void
    {
        $field = CustomField::query()->create([
            'label' => 'Token',
            'name' => 'token',
            'type' => CustomFieldType::TEXT,
            'status' => BaseStatusEnum::PUBLISHED,
            'display_location' => DisplayLocation::LOGIN,
        ]);

        $rules = apply_filters('ecommerce_customer_login_form_validation_rules', []);

        $this->assertArrayHasKey("extras.custom_fields.{$field->getKey()}", $rules);
    }

    public function test_file_field_validation_rules_for_register(): void
    {
        $field = CustomField::query()->create([
            'label' => 'Document',
            'name' => 'document',
            'type' => CustomFieldType::FILE,
            'status' => BaseStatusEnum::PUBLISHED,
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $rules = apply_filters('ecommerce_customer_registration_form_validation_rules', []);

        $fieldRules = $rules["extras.custom_fields.{$field->getKey()}"];
        $this->assertContains('nullable', $fieldRules);
        $this->assertContains('file', $fieldRules);
    }

    public function test_no_custom_field_rules_when_no_fields_exist(): void
    {
        $rules = apply_filters('ecommerce_customer_registration_form_validation_rules', []);

        $customFieldRules = array_filter(
            $rules,
            fn ($key) => str_starts_with($key, 'extras.custom_fields.'),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertEmpty($customFieldRules);
    }

    public function test_draft_fields_excluded_from_validation_rules(): void
    {
        $field = CustomField::query()->create([
            'label' => 'Draft Field',
            'name' => 'draft_field',
            'type' => CustomFieldType::TEXT,
            'status' => BaseStatusEnum::DRAFT,
            'display_location' => DisplayLocation::REGISTER,
        ]);

        $rules = apply_filters('ecommerce_customer_registration_form_validation_rules', []);

        $this->assertArrayNotHasKey("extras.custom_fields.{$field->getKey()}", $rules);
    }
}
