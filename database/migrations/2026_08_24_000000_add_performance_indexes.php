<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->index('city', 'people_city_idx');
            $table->index('gender', 'people_gender_idx');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index(['person_id', 'brand', 'model'], 'vehicles_person_brand_model_idx');
            $table->index('brand', 'vehicles_brand_idx');
            $table->index('year', 'vehicles_year_idx');
        });

        Schema::table('revisions', function (Blueprint $table) {
            $table->index(['vehicle_id', 'revision_date'], 'revisions_vehicle_date_idx');
            $table->index('revision_date', 'revisions_date_idx');
        });

        // The client login normalizes the e-mail before comparing it.
        DB::statement(
            'CREATE INDEX people_client_login_idx ON people ((LOWER(TRIM(email))), birth_date)'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS people_client_login_idx');

        Schema::table('revisions', function (Blueprint $table) {
            $table->dropIndex('revisions_vehicle_date_idx');
            $table->dropIndex('revisions_date_idx');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_person_brand_model_idx');
            $table->dropIndex('vehicles_brand_idx');
            $table->dropIndex('vehicles_year_idx');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('people_city_idx');
            $table->dropIndex('people_gender_idx');
        });
    }
};
