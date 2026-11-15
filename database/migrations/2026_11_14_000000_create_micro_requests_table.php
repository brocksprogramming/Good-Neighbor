<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('micro_requests')) {
            Schema::create('micro_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('item_name', 120);
                $table->text('description')->nullable();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->enum('urgency', ['low', 'medium', 'high', 'immediate'])->default('medium');
                $table->enum('status', ['open', 'claimed', 'fulfilled', 'cancelled'])->default('open');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('micro_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('micro_requests', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('micro_requests', 'item_name')) {
                    $table->string('item_name', 120)->default('');
                }
                if (! Schema::hasColumn('micro_requests', 'description')) {
                    $table->text('description')->nullable();
                }
                if (! Schema::hasColumn('micro_requests', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable();
                }
                if (! Schema::hasColumn('micro_requests', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable();
                }
                if (! Schema::hasColumn('micro_requests', 'urgency')) {
                    $table->enum('urgency', ['low', 'medium', 'high', 'immediate'])->default('medium');
                }
                if (! Schema::hasColumn('micro_requests', 'status')) {
                    $table->enum('status', ['open', 'claimed', 'fulfilled', 'cancelled'])->default('open');
                }
                if (! Schema::hasColumn('micro_requests', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable();
                }
                if (! Schema::hasColumn('micro_requests', 'created_at')) {
                    $table->timestamps();
                } elseif (! Schema::hasColumn('micro_requests', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (! Schema::hasIndex('micro_requests', ['status', 'latitude', 'longitude'])) {
            Schema::table('micro_requests', function (Blueprint $table) {
                $table->index(['status', 'latitude', 'longitude']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('micro_requests');
    }
};
