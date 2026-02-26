<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_custom_fields', function (Blueprint $table) {
            $table->string('apply_to', 20)->default('all')->after('display_location');
            $table->json('product_ids')->nullable()->after('apply_to');
        });
    }

    public function down(): void
    {
        Schema::table('ec_custom_fields', function (Blueprint $table) {
            $table->dropColumn(['apply_to', 'product_ids']);
        });
    }
};
