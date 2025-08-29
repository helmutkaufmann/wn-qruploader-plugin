<?php

namespace Mercator\QrUploader\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mercator_qruploader_upload_links', function ($table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('target_directory');
            $table->string('short_code')->unique()->index();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('password_hash')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mercator_qruploader_upload_links');
    }
};
