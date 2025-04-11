<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 't_stocks';

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
    ];
}
