<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'updated_by',
    ];

    /**
     * Relationship: User who updated the setting.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Static helper to get a setting value.
     */
    public static function get(string $key, $default = null): ?string
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Static helper to set a setting value.
     */
    public static function set(string $key, ?string $value, ?int $userId = null): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => $userId]
        );
    }
}
