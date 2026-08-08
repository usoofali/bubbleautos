<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Enums\TimelineEventType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Note;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access and staff administration.',
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Operational oversight and management capabilities.',
        ]);

        $staffRole = Role::create([
            'name' => 'Staff',
            'slug' => 'staff',
            'description' => 'Operational access for processing orders, documents, and payments.',
        ]);

        // 2. Permissions
        $permissions = [
            // Orders
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'Orders'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'group' => 'Orders'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'group' => 'Orders'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'group' => 'Orders'],

            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'Customers'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'group' => 'Customers'],
            ['name' => 'Edit Customers', 'slug' => 'customers.edit', 'group' => 'Customers'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'group' => 'Customers'],

            // Documents
            ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'group' => 'Documents'],
            ['name' => 'Delete Documents', 'slug' => 'documents.delete', 'group' => 'Documents'],

            // Invoices
            ['name' => 'View Invoices', 'slug' => 'invoices.view', 'group' => 'Invoices'],
            ['name' => 'Manage Invoice Items', 'slug' => 'invoices.manage_items', 'group' => 'Invoices'],

            // Payments
            ['name' => 'Create Payments', 'slug' => 'payments.create', 'group' => 'Payments'],
            ['name' => 'Update Payment Status', 'slug' => 'payments.update_status', 'group' => 'Payments'],
            ['name' => 'Delete Payments', 'slug' => 'payments.delete', 'group' => 'Payments'],

            // Emails
            ['name' => 'Review Emails', 'slug' => 'emails.review', 'group' => 'Emails'],

            // Staff & Roles & Settings
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'Users'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'Roles'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'Settings'],
        ];

        $createdPerms = [];
        foreach ($permissions as $p) {
            $createdPerms[$p['slug']] = Permission::create($p);
        }

        // Attach all to Admin
        $adminRole->permissions()->sync(array_column($createdPerms, 'id'));

        // Manager gets most permissions except Users/Roles/Settings
        $managerPerms = collect($createdPerms)->reject(function ($perm) {
            return in_array($perm->group, ['Users', 'Roles', 'Settings']);
        })->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPerms);

        // Staff gets operational permissions (No delete, No management)
        $staffPermSlugs = [
            'orders.view', 'orders.create', 'orders.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'documents.upload', 'invoices.view', 'invoices.manage_items',
            'payments.create', 'payments.update_status', 'emails.review',
        ];
        $staffPermIds = collect($createdPerms)
            ->filter(fn ($p) => in_array($p->slug, $staffPermSlugs))
            ->pluck('id')
            ->toArray();
        $staffRole->permissions()->sync($staffPermIds);

        // 3. Demo Users
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@bubbleautos.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $manager = User::create([
            'name' => 'Operations Manager',
            'email' => 'manager@bubbleautos.com',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);

        $staff = User::create([
            'name' => 'Front Desk Staff',
            'email' => 'staff@bubbleautos.com',
            'password' => Hash::make('password'),
            'role_id' => $staffRole->id,
            'is_active' => true,
        ]);

        // 4. Default Settings
        Setting::set('company_name', 'Bubbles Autos', 'general');
        Setting::set('company_logo', '/images/logo.png', 'general');
        Setting::set('currency_symbol', '$', 'general');
        Setting::set('time_zone', 'UTC', 'general');

        Setting::set('order_prefix', 'BA-', 'business');
        Setting::set('invoice_prefix', 'INV-', 'business');
        Setting::set('default_destination', 'Lagos, Nigeria', 'business');
        Setting::set('default_shipping_line', 'Grimaldi Lines', 'business');

        Setting::set('hero_title', 'Global Vehicle Shipment & Management System', 'website');
        Setting::set('hero_subtitle', 'Internal management portal for Bubble Autos staff to manage customer vehicle orders, VIN search, documents, invoices, and shipment tracking.', 'website');
        Setting::set('contact_phone', '+1 (800) 555-BUBBLE', 'website');
        Setting::set('contact_email', 'contact@bubbleautos.com', 'website');
        Setting::set('contact_address', '100 Shipping Way, Houston, TX 77001', 'website');

        // 5. Sample Customers
        $c1 = Customer::create([
            'name' => 'John Doe',
            'phone' => '+1 555-0192',
            'whatsapp' => '+1 555-0192',
            'email' => 'johndoe@example.com',
            'address' => '123 Palm Street, Houston TX',
            'notes' => 'VIP Client - Frequent exporter',
            'created_by' => $admin->id,
        ]);

        $c2 = Customer::create([
            'name' => 'Sarah Jenkins',
            'phone' => '+1 555-0482',
            'whatsapp' => '+1 555-0482',
            'email' => 'sarah.j@example.com',
            'address' => '456 Ocean Boulevard, Miami FL',
            'notes' => 'Requires WhatsApp status updates for all shipments',
            'created_by' => $staff->id,
        ]);

        // 6. Sample Orders & Invoices & Payments
        $o1 = Order::create([
            'order_number' => 'BA-00001',
            'vin' => '1FA6P8CF0H5123456',
            'make' => 'Ford',
            'model' => 'Mustang GT',
            'year' => 2022,
            'color' => 'Shadow Black',
            'customer_id' => $c1->id,
            'shipping_line' => 'Grimaldi Lines',
            'destination' => 'Lagos, Nigeria',
            'status' => ShipmentStatus::IN_TRANSIT,
            'expected_arrival' => now()->addDays(14),
        ]);

        $inv1 = Invoice::create([
            'order_id' => $o1->id,
            'invoice_number' => 'INV-00001',
            'subtotal' => 0,
            'discount' => 50,
            'total' => 0,
            'paid' => 0,
            'balance' => 0,
            'currency' => '$',
            'status' => 'unpaid',
        ]);

        $inv1->items()->createMany([
            ['description' => 'Ocean Freight Shipping Charge', 'quantity' => 1, 'unit_price' => 1450.00, 'amount' => 1450.00],
            ['description' => 'Port Handling & Dock Documentation Fee', 'quantity' => 1, 'unit_price' => 250.00, 'amount' => 250.00],
            ['description' => 'Customs Title Clearance', 'quantity' => 1, 'unit_price' => 150.00, 'amount' => 150.00],
        ]);

        $inv1->payments()->create([
            'amount' => 1000.00,
            'payment_date' => now()->subDays(2)->toDateString(),
            'method' => PaymentMethod::WIRE,
            'reference' => 'WIRE-992011',
            'notes' => 'Initial 50% deposit received',
            'recorded_by' => $staff->id,
        ]);

        TimelineEvent::create([
            'order_id' => $o1->id,
            'user_id' => $admin->id,
            'event_type' => TimelineEventType::ORDER_CREATED,
            'title' => 'Order Created',
            'description' => 'Order BA-00001 registered for 2022 Ford Mustang GT (VIN: 1FA6P8CF0H5123456).',
            'created_at' => now()->subDays(5),
        ]);

        TimelineEvent::create([
            'order_id' => $o1->id,
            'user_id' => $staff->id,
            'event_type' => TimelineEventType::PAYMENT_RECEIVED,
            'title' => 'Payment Received',
            'description' => 'Recorded wire payment of $1,000.00. Remaining balance: $800.00.',
            'created_at' => now()->subDays(2),
        ]);

        Note::create([
            'order_id' => $o1->id,
            'user_id' => $staff->id,
            'content' => 'Customer called to confirm vehicle container loading date.',
        ]);

    }
}
