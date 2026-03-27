<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price_chf',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_chf' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
