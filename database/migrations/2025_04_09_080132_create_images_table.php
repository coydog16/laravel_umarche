<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id') //外部キー参照
            ->constrained() // 必須
            ->onUpdate('cascade') // オーナーIDの変更があった際はこのデータも変更されるcascade
            ->onDelete('cascade'); // ”削除される。
            $table->string('filename');
            $table->string('title')->nullable(); //空でも登録できるようにnullable()を付けておく
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
