<?php

use App\Enums\GmailSearchOperator;
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
        Schema::create('email_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('operator', GmailSearchOperator::class::listOperators());
            $table->string('value');

            // Add a composite unique key on 'user_id', 'operator', and 'value'
            $table->unique(['user_id', 'operator', 'value'], 'email_filters_user_operator_value_unique');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_filters');
    }
};
