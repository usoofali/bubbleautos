<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Setting::class);

        return Inertia::render('Settings/Index', [
            'general' => $this->settingService->getGroup('general'),
            'business' => $this->settingService->getGroup('business'),
            'website' => $this->settingService->getGroup('website'),
            'email' => $this->settingService->getGroup('email'),
        ]);
    }

    public function updateGroup(Request $request, string $group): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $this->settingService->updateGroup($group, $validated['settings']);

        ActivityLogService::log(
            'setting.updated',
            "Updated {$group} configuration settings"
        );

        return back()->with('success', ucfirst($group).' settings updated successfully.');
    }
}
