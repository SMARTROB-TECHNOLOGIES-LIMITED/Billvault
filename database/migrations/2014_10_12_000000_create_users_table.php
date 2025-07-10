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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('other_name')->nullable();
            $table->string('username')->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('dob')->nullable();
            $table->string('account_number')->nullable();
            $table->string('paystack_id')->nullable();
            $table->string('transaction_pin')->nullable();
            $table->float('balance',10,2)->default(0.00);
            $table->integer('account_level')->default(1);
            $table->string('passport')->nullable();
            $table->string('profile')->nullable()->default('default.jpg');
            $table->string('gender')->nullable();
            $table->string('bvn')->nullable();
            $table->text('address')->nullable();
            $table->integer('complete')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->integer('view')->default(1);
            $table->string('email_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
