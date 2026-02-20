<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes; // Karena di kodenya ada deleted_at

    protected $fillable = ['code', 'name', 'credits'];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
