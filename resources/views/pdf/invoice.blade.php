<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 30px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .logo-img {
            max-height: 120px;
            width: auto;
            margin-bottom: 8px;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-details {
            font-size: 11px;
            color: #64748b;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: 900;
            color: #0f172a;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }
        .invoice-subtitle {
            font-size: 13px;
            font-weight: bold;
            color: #475569;
            text-align: right;
            font-family: monospace;
        }
        .balance-box {
            text-align: right;
            margin-top: 10px;
        }
        .balance-label {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .balance-amount {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }
        .details-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .bill-to-title {
            font-size: 11px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .customer-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
        }
        .meta-label {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
        }
        .meta-val {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border: 1px solid #0f172a;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .totals-table {
            width: 280px;
            margin-left: auto;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 12px;
        }
        .totals-label {
            color: #64748b;
            font-weight: 500;
        }
        .totals-amount {
            font-weight: bold;
            font-family: monospace;
            text-align: right;
        }
        .total-row td {
            border-top: 1px solid #cbd5e1;
            font-weight: 800;
            font-size: 14px;
            color: #0f172a;
        }
        .balance-row td {
            background-color: #f1f5f9;
            font-weight: 900;
            font-size: 14px;
            color: #0f172a;
            padding: 8px 10px;
        }
        .footer-notes {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Header Row -->
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                @if(file_exists(public_path('logo.jpeg')))
                    <img src="{{ public_path('logo.jpeg') }}" class="logo-img" alt="Logo" />
                @endif
                <div class="company-title" style="margin-top: 5px;">{{ $companySettings['name'] ?? 'BUBBLES AUTOS' }}</div>
                <div class="company-details">{{ $companySettings['address'] ?? '100 Shipping Way, Houston, TX 77001' }}</div>
                <div class="company-details">{{ $companySettings['email'] ?? 'contact@bubbleautos.com' }} | {{ $companySettings['phone'] ?? '+1 (800) 555-BUBBLE' }}</div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <h1 class="invoice-title">INVOICE</h1>
                <div class="invoice-subtitle">Invoice# {{ $order->order_number }}</div>
                <div class="balance-box">
                    <div class="balance-label">Balance Due</div>
                    <div class="balance-amount">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->balance ?? 0, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bill To & Order Metadata -->
    <table class="details-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="bill-to-title">Bill To</div>
                <div class="customer-name">{{ $order->customer->name ?? 'Valued Customer' }}</div>
                @if(!empty($order->customer->address))
                    <div style="font-size: 11px; color: #475569;">{{ $order->customer->address }}</div>
                @endif
                @if(!empty($order->customer->phone))
                    <div style="font-size: 11px; color: #475569;">Phone: {{ $order->customer->phone }}</div>
                @endif
                @if(!empty($order->customer->email))
                    <div style="font-size: 11px; color: #475569;">Email: {{ $order->customer->email }}</div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="meta-label" style="text-align: right;">Invoice Date:</td>
                        <td class="meta-val" style="text-align: right; width: 110px;">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label" style="text-align: right;">Due Date:</td>
                        <td class="meta-val" style="text-align: right;">{{ !empty($order->expected_arrival) ? \Carbon\Carbon::parse($order->expected_arrival)->format('M d, Y') : \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label" style="text-align: right;">Vehicle:</td>
                        <td class="meta-val" style="text-align: right;">{{ trim(($order->year ? $order->year . ' ' : '') . ($order->make ?? '') . ' ' . ($order->model ?? '')) }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label" style="text-align: right;">VIN:</td>
                        <td class="meta-val" style="text-align: right; font-family: monospace;">{{ $order->vin }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Itemized Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th>Description / Item Details</th>
                <th style="width: 140px;" class="text-right">Amount ({{ $companySettings['currency_code'] ?? 'USD' }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->invoice->items ?? [] as $index => $item)
                <tr>
                    <td class="text-center" style="color: #64748b; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold; color: #0f172a;">{{ $item->description }}</td>
                    <td class="text-right" style="font-family: monospace; font-weight: bold;">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($item->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #94a3b8; font-style: italic; padding: 20px;">No invoice line items added.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals Breakdown -->
    <table class="totals-table">
        <tr>
            <td class="totals-label">Sub Total:</td>
            <td class="totals-amount">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->total ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label" style="color: #059669;">Payments Received:</td>
            <td class="totals-amount" style="color: #059669;">-{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->paid ?? 0, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Invoice Amount:</td>
            <td class="totals-amount">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->total ?? 0, 2) }}</td>
        </tr>
        <tr class="balance-row">
            <td>Balance Due:</td>
            <td class="totals-amount">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->balance ?? 0, 2) }}</td>
        </tr>
    </table>

    <!-- Notes -->
    <div class="footer-notes">
        <strong style="color: #334155;">Notes:</strong><br />
        Thank you for doing business with Bubbles Autos. All vehicle shipment inquiries should reference Order# {{ $order->order_number }}.
    </div>
</body>
</html>
