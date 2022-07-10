<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalConfigCreateConfigPrefixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gc_config_prefixes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 255)->comment('类型'); // 参见模型内 TYPE_* 常量
            $table->string('key', 255)->comment('键名');
            $table->string('name', 255)->comment('语义化名');

            $table->unsignedInteger('deleted_by')->default(0)->comment('删除人');
            $table->unsignedInteger('created_at')->nullable();
            $table->unsignedInteger('updated_at')->nullable();
            $table->unsignedInteger('deleted_at')->nullable();

            $table->unique(['key', 'type']);
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
