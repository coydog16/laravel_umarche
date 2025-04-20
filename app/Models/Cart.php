<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [ //このプロパティのみ変更可の宣言をする
        'user_id',
        'product_id',
        'quantity',
    ];
}
