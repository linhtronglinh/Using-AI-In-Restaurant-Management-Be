<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('khu_vucs', function (Blueprint $table) {
            $table->id();
            $table->string('name_area');
            $table->string('slug_area');
            $table->integer('status');
            $table->string('list_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('khu_vucs');
    }
};
