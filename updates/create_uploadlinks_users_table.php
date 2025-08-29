<?php namespace Mercator\QrUploader\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateUploadlinksUsersTable extends Migration
{
    public function up()
    {
        Schema::create('mercator_qruploader_uploadlinks_users', function($table) {
            $table->engine = 'InnoDB';
            // IMPORTANT: Change 'upload_link_id' if your primary key is named differently.
            $table->integer('upload_link_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->primary(['upload_link_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mercator_qruploader_uploadlinks_users');
    }
}
