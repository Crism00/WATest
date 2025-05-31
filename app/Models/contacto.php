<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contacto extends Model
{
    protected $table = 'contactos';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'course',
        'last_name',
        'zoho_id', // Almacenar el ID de Zoho
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
