<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        $settings = Setting::where('group', 'website')->get()->pluck('value', 'key')->toArray();
        $general = Setting::where('group', 'general')->get()->pluck('value', 'key')->toArray();

        return Inertia::render('Welcome', [
            'cms' => array_merge([
                'company_name' => $general['company_name'] ?? 'Bubbles Autos',
                'company_logo' => $general['company_logo'] ?? '/images/logo.png',
                'hero_title' => 'Global Vehicle Shipment & Management System',
                'hero_subtitle' => 'Internal management portal for Bubble Autos staff to manage customer vehicle orders, VIN search, documents, invoices, and shipment tracking.',
                'contact_phone' => '+1 (800) 555-BUBBLE',
                'contact_email' => 'contact@bubbleautos.com',
                'contact_address' => '100 Shipping Way, Houston, TX 77001',
            ], $settings),
        ]);
    }
}
