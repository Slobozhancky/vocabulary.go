<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('words', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id');
        $table->string('word');
        $table->string('translation')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('words');
}

};
