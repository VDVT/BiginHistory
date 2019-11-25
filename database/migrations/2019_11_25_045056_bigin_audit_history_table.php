<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BiginAuditHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_histories', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('activity_id')->nullable();
            $table->text('details')->nullable();
            $table->string('type')->unsigned()->nullable();
            $table->integer('result')->unsigned()->nullable();
            $table->string('target_type')->nullable();
            $table->integer('target_id')->nullable();
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_histories');
    }
}
