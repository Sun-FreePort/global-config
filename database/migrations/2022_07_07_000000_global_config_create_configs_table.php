<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalConfigCreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gc_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary()->autoIncrement();
            $table->unsignedInteger('group_id')->comment('组 ID');
            $table->unsignedInteger('prefix_id')->comment('前缀 ID');
            $table->string('key', 36)->comment('配置名');
            $table->string('value')->comment('配置值');
            $table->string('desc', 144)->default('')->comment('配置值');
            $table->unsignedTinyInteger('type')->comment('值类型');
            $table->string('rules', 300)->comment('规则集');
            $table->unsignedTinyInteger('cache')->default(1)->comment('缓存支持');
            $table->unsignedTinyInteger('active')->default(1)->comment('启停用');
            $table->softDeletes();
            $table->timestamps();
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
