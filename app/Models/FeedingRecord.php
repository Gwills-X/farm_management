<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'fed_at',
        'feed_type',
        'quantity',
        'unit',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'fed_at' => 'datetime',
        'quantity' => 'decimal:2',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
