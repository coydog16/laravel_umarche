<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PrimaryCategory;

class SecondaryCategory extends Model
{
    public function Primary()
    {
        return $this->belongsTo(PrimaryCategory::class);

    }
}
