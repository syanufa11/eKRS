<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import ini

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = ['nim', 'name', 'email'];

    protected $dates = ['deleted_at'];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
