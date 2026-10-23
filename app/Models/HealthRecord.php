<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'observation_date',
        'observed_condition',
        'health_status',
        'treatment',
        'medication',
        'veterinary_professional',
        'follow_up_date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'follow_up_date' => 'date',
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
