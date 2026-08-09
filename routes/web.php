<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailInboxController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', WelcomeController::class)->name('home');
Route::get('/api/public/vin-lookup', [WelcomeController::class, 'lookupVin'])->name('public.vin-lookup');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard & Search API
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/search', [GlobalSearchController::class, 'search'])->name('api.search');

    // Vehicle Orders & Centerpiece Workspace
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('/orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.update-tracking');
    Route::post('/orders/{order}/sync-vehicle-api', [OrderController::class, 'syncVehicleApi'])->name('orders.sync-vehicle');
    Route::get('/api/vehicles/{vin}/lookup', [OrderController::class, 'lookupVin'])->name('orders.lookup-vin');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Invoice & Line Items
    Route::post('/orders/{invoice}/invoice/items', [InvoiceController::class, 'addItem'])->name('invoices.items.store');
    Route::patch('/invoice-items/{item}', [InvoiceController::class, 'updateItem'])->name('invoices.items.update');
    Route::delete('/invoice-items/{item}', [InvoiceController::class, 'removeItem'])->name('invoices.items.destroy');

    // Payments
    Route::post('/orders/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Documents
    Route::post('/orders/{order}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Emails Ingestion & Synchronization
    Route::get('/emails', [EmailInboxController::class, 'index'])->name('emails.index');
    Route::post('/emails/fetch', [EmailInboxController::class, 'fetchFromImap'])->name('emails.fetch');
    Route::post('/emails/{email}/link', [EmailInboxController::class, 'linkOrder'])->name('emails.link');
    Route::post('/emails/{email}/unlink', [EmailInboxController::class, 'unlinkOrder'])->name('emails.unlink');
    Route::get('/email-attachments/{attachment}/download', [EmailInboxController::class, 'downloadAttachment'])->name('email-attachments.download');

    // Staff Notes
    Route::post('/orders/{order}/notes', [NoteController::class, 'store'])->name('notes.store');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Staff Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Roles & Permissions Matrix
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::patch('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Preset Invoice Item Catalog
    Route::get('/invoice-item-templates', [App\Http\Controllers\InvoiceItemTemplateController::class, 'index'])->name('invoice-item-templates.index');
    Route::post('/invoice-item-templates', [App\Http\Controllers\InvoiceItemTemplateController::class, 'store'])->name('invoice-item-templates.store');
    Route::patch('/invoice-item-templates/{invoiceItemTemplate}', [App\Http\Controllers\InvoiceItemTemplateController::class, 'update'])->name('invoice-item-templates.update');
    Route::delete('/invoice-item-templates/{invoiceItemTemplate}', [App\Http\Controllers\InvoiceItemTemplateController::class, 'destroy'])->name('invoice-item-templates.destroy');

    // System & CMS Settings
    Route::get('/settings/system', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/{group}', [SettingController::class, 'updateGroup'])->name('settings.update-group');
});

require __DIR__.'/settings.php';
