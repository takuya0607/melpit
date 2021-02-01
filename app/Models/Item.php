<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    // 出品中
    const STATE_SELLING = 'selling';
    // 購入済み
    const STATE_BOUGHT = 'bought';

    // Eloquent Modelのcastsフィールドを使うことで、カラムの値を取り出す際に、データ型を変換させることが可能
    // キー名にカラム名を、値に変換先のデータ型を指定
    // bought_atカラムを取り出す際にdatetime(Carbonクラス)に変換
    protected $casts = [
      'bought_at' => 'datetime',
    ];

    public function secondaryCategory()
    {
      return $this->belongsTo(SecondaryCategory::class);  
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function condition()
    {
        return $this->belongsTo(ItemCondition::class, 'item_condition_id');
    }

    // 商品が出品済みかどうかを返すアクセサ
    public function getIsStateSellingAttribute()
    {
      return $this->state === self::STATE_SELLING;
    }

    // 商品が購入済みかどうかを返すアクセサ
    public function getIsStateBoughtAttribute()
    {
        return $this->state === self::STATE_BOUGHT;
    }
}
