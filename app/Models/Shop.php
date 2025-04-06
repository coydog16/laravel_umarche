<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Owner;

class Shop extends Model
{
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
