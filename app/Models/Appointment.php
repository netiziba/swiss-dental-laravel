<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'dentist_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'external_booking_id',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date:Y-m-d',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function dentist()
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function treatmentNote()
    {
        return $this->hasOne(TreatmentNote::class);
    }
}
