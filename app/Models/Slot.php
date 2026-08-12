<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'capacity',
        'remaining'
    ];

    public function holds()
    {
        return $this->hasMany(Hold::class);
    }
}
