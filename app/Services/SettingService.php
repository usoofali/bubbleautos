<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function getGroup(string $group): array
    {
        return Setting::where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    public function updateGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::set($key, $value, $group);
        }
    }
}
