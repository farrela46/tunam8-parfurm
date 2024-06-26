<?php

use App\Models\Address;
use App\Models\User;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->double('total');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->enum('status', ['unpaid', 'proccess', 'shipping', 'received', 'canceled'])->default('unpaid');
            $table->foreignIdFor(Address::class)->nullable()->constrained()->onDelete('set null');
            $table->text('tracking_number')->nullable();
            $table->double('cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
