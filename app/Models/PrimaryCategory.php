<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimaryCategory extends Model
{
    //
      public function secondaryCategories()
    {
      // 大カテゴリ(1)対小カテゴリ(多)の関係性
      // 大カテゴリは1つしかないが、小カテゴリは分類化されて多数あるため
      // これにより$category->secondaryCategoriesで大カテゴリに紐づく小カテゴリの一覧が取得できる
      return $this->hasMany(SecondaryCategory::class);
    }
}
