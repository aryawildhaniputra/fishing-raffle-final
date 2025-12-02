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
        Schema::create('participant_groups', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("phone_num");
            $table->foreignId("event_id")->constrained("events");
            $table->integer("total_member");
            $table->enum("stall_order_type", ["under", "upper"])->nullable();
            $table->enum("status", ["unpaid", "dp", "paid"])->default("unpaid");
            $table->enum("raffle_status", ["not_yet", "completed"])->default("not_yet");
            $table->text("information")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_group');
    }
};
