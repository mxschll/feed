<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
    ];

    function feeds() {
        return $this->belongsToMany(Feed::class);
    }
}
