<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistAvailability extends Model
{
    protected $table = 'dentist_availability';

    protected $fillable = [
        'dentist_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'day_of_week' => 'integer',
        ];
    }

    public function dentist()
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }
}
