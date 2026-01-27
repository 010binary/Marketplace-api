<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("product_images", function (Blueprint $table) {
            $table->id();
            $table->foreignId("product_id")->constrained()->cascadeOnDelete();
            $table->string("disk")->default("private");
            $table->string("path");
            $table->string("mime_type");
            $table->bigInteger("size")->unsigned();
            $table->timestamps();

            $table->unique("product_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("product_images");
    }
};
