<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalConfigCreateConfigKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gc_config_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('group_id')->comment('组 ID');
            $table->unsignedInteger('prefix_id')->comment('前缀 ID');
            $table->string('key', 255)->comment('配置键');
            $table->string('key_full', 255)->comment('配置全键');
            $table->string('value')->comment('配置值');
            $table->string('desc', 255)->default('')->comment('描述');
            $table->unsignedTinyInteger('type')->comment('值类型');
            $table->string('rules', 300)->default('{}')->comment('规则集'); // 暂无
            $table->unsignedTinyInteger('cache')->default(1)->comment('缓存支持');
            $table->unsignedTinyInteger('active')->default(1)->comment('启停用');
            $table->unsignedTinyInteger('visible')->default(1)->comment('可暴露');

            $table->unsignedInteger('deleted_by')->default(0)->comment('删除人 ID');
            $table->unsignedInteger('created_at')->nullable();
            $table->unsignedInteger('updated_at')->nullable();
            $table->unsignedInteger('deleted_at')->nullable();

            $table->index(['group_id', 'prefix_id', 'key']);
            $table->index('key_full');
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
