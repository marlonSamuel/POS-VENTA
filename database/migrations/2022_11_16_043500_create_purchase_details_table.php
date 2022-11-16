<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('raw_material_id');
            $table->integer('quantity');
            $table->decimal('purchase_price',8,2);
            $table->decimal('discount',8,2);
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_details');
    }
};
