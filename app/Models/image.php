<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Owner;

class image extends Model
{
    protected $fillable = [ //これ以外のプロパティは変更できないと宣言するプロパティ
        'owner_id',
        'filename',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
