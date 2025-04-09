<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class image extends Model
{
    protected $fillable = [ //これ以外のプロパティは変更できないと宣言するプロパティ
        'owner_id',
        'filename',
    ];
}
