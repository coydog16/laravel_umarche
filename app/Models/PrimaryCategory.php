<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SecondaryCategory;

class PrimaryCategory extends Model
{
    public function Secondary()
    {
        return $this->hasMany(SecondaryCategory::class);
    }
}
