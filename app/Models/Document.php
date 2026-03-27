<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'file_path',
        'file_type',
        'category',
    ];

    protected $appends = ['file_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
