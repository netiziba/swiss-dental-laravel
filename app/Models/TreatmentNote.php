<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentNote extends Model
{
    protected $fillable = [
        'appointment_id',
        'dentist_id',
        'patient_id',
        'diagnosis',
        'treatment',
        'notes',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function dentist()
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
