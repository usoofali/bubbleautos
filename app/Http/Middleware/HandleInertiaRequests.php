<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Handle the incoming request and prevent raw JSON rendering on mobile tab restores.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        // Instruct browser caches (especially mobile Safari/Chrome bfcache) that JSON and HTML vary by X-Inertia header
        $response->headers->set('Vary', 'X-Inertia', false);

        if ($request->header('X-Inertia')) {
            $response->headers->set('Cache-Control', 'no-cache, max-age=0, must-revalidate, no-store');
        }

        return $response;
    }

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'appName' => Setting::get('company_name', 'Bubbles Autos'),
            'currencySymbol' => Setting::get('currency_symbol', '$'),
            'currencyCode' => Setting::get('currency_code', 'USD'),
            'company' => [
                'name' => Setting::get('company_name', 'Bubbles Autos'),
                'logo' => Setting::get('company_logo', '/logo.jpeg'),
                'address' => Setting::get('contact_address', '100 Shipping Way, Houston, TX 77001'),
                'email' => Setting::get('contact_email', 'contact@bubbleautos.com'),
                'phone' => Setting::get('contact_phone', '+1 (800) 555-BUBBLE'),
                'currency_symbol' => Setting::get('currency_symbol', '$'),
                'currency_code' => Setting::get('currency_code', 'USD'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->isAdmin(),
                    'role' => $user->role ? [
                        'name' => $user->role->name,
                        'slug' => $user->role->slug,
                    ] : null,
                    'permissions' => $user->permissionSlugs(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
