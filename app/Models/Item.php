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

    public function secondaryCategory()
    {
      return $this->belongsTo(SecondaryCategory::class);
    }

    public function getIsStateSellingAttribute()
    {
      return $this->state === self::STATE_SELLING;
    }
}
