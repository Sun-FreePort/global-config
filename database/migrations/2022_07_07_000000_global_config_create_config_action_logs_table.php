<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalConfigCreateConfigActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gc_config_action_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('belong_id')->comment('操作项 ID');
            $table->unsignedBigInteger('author_id')->comment('操作人 ID');
            $table->string('type', 255)->comment('操作类型');
            $table->unsignedTinyInteger('actions')->comment('操作行为（二进制）'); // eg. 011 => 3 / 101 => 5
            $table->string('snapshot', 400)->comment('快照'); // delete is special upgrade.

            $table->unsignedInteger('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
