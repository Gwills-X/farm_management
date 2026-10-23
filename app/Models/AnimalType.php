<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AnimalType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code_prefix',
        'description',
        'is_active',
    ];

    // code_sequence is managed only by AnimalCodeGenerator, never by mass assignment.
    protected $casts = [
        'is_active' => 'boolean',
        'code_sequence' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (AnimalType $type) {
            if (blank($type->slug)) {
                $type->slug = Str::slug($type->name);
            }

            if (blank($type->code_prefix)) {
                $type->code_prefix = static::makePrefix($type->name);
            }

            $type->code_prefix = strtoupper($type->code_prefix);
        });
    }

    public static function makePrefix(string $name): string
    {
        $letters = preg_replace('/[^A-Za-z]/', '', $name) ?? '';

        return strtoupper(substr($letters, 0, 3)) ?: 'ANM';
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
