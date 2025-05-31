<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'name',
    ];

    public function contactos()
    {
        return $this->hasMany(contacto::class, 'course_id');
    }
}
