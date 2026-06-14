<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealRequest extends Model
{
    protected $fillable = [
        'user_id',
        'meal_type_id',
        'request_date',
        'submitted_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'request_date' => 'date:Y-m-d',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Relationship: User who made the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: The meal type requested.
     */
    public function mealType()
    {
        return $this->belongsTo(MealType::class);
    }
}
