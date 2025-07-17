<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tier_threes', function (Blueprint $table) {
            $table->string('dl_stateOfIssue')->after('rejection_reason')->nullable();
            $table->string('dl_expiryDate')->after('dl_stateOfIssue')->nullable();
            $table->string('dl_issuedDate')->after('dl_expiryDate')->nullable();
            $table->string('dl_licenseNo')->after('dl_issuedDate')->nullable();
            $table->string('dl_uuid')->after('dl_licenseNo')->nullable();

            $table->string('verification_image')->after('dl_uuid')->nullable();
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
        Schema::table('tier_three', function (Blueprint $table) {
            //
        });
    }
};
