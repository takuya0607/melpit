<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // upメソッドはマイグレーション実行時に呼び出され、テーブルの作成やカラムの追加を行う
    public function up()
    {
        Schema::create('primary_categories', function (Blueprint $table) {
            $table->id();

             // ここにカラムを追加していく

            $table->timestamps();
        });

        Schema::create('secondary_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('primary_category_id');

             // ここにカラムを追加していく

            $table->timestamps();
            // primary_category_idはprimary_categoriesテーブルのidを参照する
            // 外部キーを定義する際、参照先のテーブルがすでに作成されている必要があるので記述順に注意する。
            $table->foreign('primary_category_id')->references('id')->on('primary_categories');
        });

        Schema::create('item_conditions', function (Blueprint $table) {
            $table->id();

             // ここにカラムを追加していく

            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('secondary_category_id');
            $table->unsignedBigInteger('item_condition_id');

             // ここにカラムを追加していく

            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('users');
            $table->foreign('buyer_id')->references('id')->on('users');
            $table->foreign('secondary_category_id')->references('id')->on('secondary_categories');
            $table->foreign('item_condition_id')->references('id')->on('item_conditions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    // downメソッドはマイグレーションを取り消し（ロールバック）する際に呼び出され、upで作成したテーブルやカラムを削除する
    public function down()
    {
      Schema::dropIfExists('items');
      Schema::dropIfExists('item_conditions');
      Schema::dropIfExists('secondary_categories');
      Schema::dropIfExists('primary_categories');
    }
}
