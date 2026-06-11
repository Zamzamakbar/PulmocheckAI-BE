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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('result')->nullable();
            $table->string('image')->nullable();
            
            // Kolom CNN
            $table->float('cnn_accuracy')->nullable();
            $table->float('cnn_auc')->nullable();
            $table->string('cnn_label')->nullable();

            // Kolom ViT
            $table->float('vit_accuracy')->nullable();
            $table->float('vit_auc')->nullable();
            $table->string('vit_label')->nullable();
            $table->string('validation_doctor')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
};
