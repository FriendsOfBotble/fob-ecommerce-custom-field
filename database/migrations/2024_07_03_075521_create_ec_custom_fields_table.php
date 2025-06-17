<?php

use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_custom_fields')) {
            Schema::create('ec_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->string('name');
                $table->string('placeholder')->nullable();
                $table->string('type', 60)->default('text');
                $table->string('status', 60)->default(BaseStatusEnum::PUBLISHED);
                $table->text('options')->nullable();
                $table->string('display_location', 60);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ec_custom_field_values')) {
            Schema::create('ec_custom_field_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('custom_field_id');
                $table->morphs('model');
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ec_custom_fields_translations')) {
            Schema::create('ec_custom_fields_translations', function (Blueprint $table) {
                $table->foreignId('ec_custom_fields_id');
                $table->string('lang_code');
                $table->string('label')->nullable();
                $table->string('placeholder')->nullable();
                $table->text('options')->nullable();

                $table->primary(['lang_code', 'ec_custom_fields_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_custom_fields');
        Schema::dropIfExists('ec_custom_field_values');
        Schema::dropIfExists('ec_custom_fields_translations');
    }
};
