<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tier_twos', function (Blueprint $table) {
            $table->string('nin')->after('rejection_reason')->nullable();
            $table->string('verification_image')->after('nin')->nullable();
            $table->string('selfie')->after('verification_image')->nullable();
            $table->string('selfie_match')->after('selfie')->nullable();
            $table->string('selfie_confidence')->after('selfie_match')->nullable();
            $table->string('nationality')->after('selfie_confidence')->nullable();
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tier_two', function (Blueprint $table) {
            //
        });
    }
};
