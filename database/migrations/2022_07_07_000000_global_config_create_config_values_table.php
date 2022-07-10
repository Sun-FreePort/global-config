<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalConfigCreateConfigValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gc_config_values', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('key_full', 255)->comment('全键');
            $table->string('key', 255)->comment('键');
            $table->unsignedBigInteger('key_id')->comment('键 ID');
            $table->string('value')->comment('值'); // Struct: Type|RealValue
            $table->unsignedInteger('author_by')->comment('最近操作人');

            $table->unsignedInteger('created_at')->nullable();
            $table->unsignedInteger('updated_at')->nullable();
            $table->unsignedInteger('deleted_at')->nullable();

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
