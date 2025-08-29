<?php

namespace Mercator\QrUploader\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mercator_qruploader_upload_links', function($table) {
            $table->string('allowed_extensions')->nullable()->after('password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('mercator_qruploader_upload_links', function($table) {
            $table->dropColumn('allowed_extensions');
        });
    }
};
