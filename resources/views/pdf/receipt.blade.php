<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt {{ $payment->reference ?? ('REC-' . $payment->id) }}</title>
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
            border-bottom: 3px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .logo-img {
            max-height: 120px;
            width: auto;
            margin-bottom: 8px;
        }

        .company-title {
            font-size: 18px;
            font-weight: 800;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-details {
            font-size: 11px;
            color: #475569;
        }

        .receipt-title {
            font-size: 24px;
            font-weight: 900;
            color: #047857;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #059669;
            text-align: right;
            font-family: monospace;
            margin-top: 3px;
        }

        .amount-box {
            background-color: #ecfdf5;
            border: 2px solid #34d399;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            margin-bottom: 25px;
        }

        .amount-label {
            font-size: 11px;
            font-weight: 900;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .amount-value {
            font-size: 36px;
            font-weight: 900;
            color: #047857;
            font-family: monospace;
            margin-top: 5px;
        }

        .grid-table {
            width: 100%;
            border: 1px solid #a7f3d0;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
        }

        .grid-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #d1fae5;
            border-right: 1px solid #d1fae5;
            width: 50%;
            background-color: #ffffff;
        }

        .grid-table tr:nth-child(even) td {
            background-color: #f0fdf4;
        }

        .grid-label {
            font-size: 10px;
            font-weight: 800;
            color: #059669;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .grid-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }

        .footer-table {
            width: 100%;
            border-top: 2px solid #a7f3d0;
            padding-top: 15px;
            font-size: 11px;
            color: #475569;
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
                <div class="company-title" style="margin-top: 5px;">{{ $companySettings['name'] ?? 'BUBBLES AUTOS' }}
                </div>
                <div class="company-details">{{ $companySettings['address'] ?? '100 Shipping Way, Houston, TX 77001' }}
                </div>
                <div class="company-details">{{ $companySettings['email'] ?? 'contact@bubbleautos.com' }} |
                    {{ $companySettings['phone'] ?? '+1 (800) 555-BUBBLE' }}</div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <h1 class="receipt-title">OFFICIAL PAYMENT RECEIPT</h1>
                <div class="receipt-subtitle">Receipt Ref: {{ $payment->reference ?? ('REC-' . $payment->id) }}</div>
                <div class="receipt-subtitle">Order#: {{ $order->order_number }}</div>
            </td>
        </tr>
    </table>

    <!-- Sum Received Banner -->
    <div style="font-size: 13px; margin-bottom: 15px;">
        Received with thanks from <strong
            style="color: #047857; font-size: 14px;">{{ $order->customer->name ?? 'Valued Customer' }}</strong> the sum of:
    </div>

    <div class="amount-box">
        <div class="amount-label">AMOUNT PAID</div>
        <div class="amount-value">
            {{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($payment->amount, 2) }}</div>
    </div>

    <!-- Payment Details Grid -->
    <table class="grid-table">
        <tr>
            <td>
                <span class="grid-label">Payment Date</span>
                <span class="grid-val">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</span>
            </td>
            <td>
                <span class="grid-label">Payment Method</span>
                <span
                    class="grid-val">{{ is_object($payment->method) ? $payment->method->label() : ucwords(str_replace('_', ' ', (string) ($payment->method ?? 'Payment'))) }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="grid-label">For Vehicle Shipment</span>
                <span class="grid-val" style="font-family: monospace; color: #047857;">
                    {{ trim(($order->year ? $order->year . ' ' : '') . ($order->make ?? '') . ' ' . ($order->model ?? '')) }}
                    (VIN: {{ $order->vin }})
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="grid-label">Invoice Total</span>
                <span
                    class="grid-val">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->total ?? 0, 2) }}</span>
            </td>
            <td>
                <span class="grid-label">Remaining Balance</span>
                <span class="grid-val"
                    style="color: #2563eb;">{{ $companySettings['currency_symbol'] ?? '$' }}{{ number_format($order->invoice->balance ?? 0, 2) }}</span>
            </td>
        </tr>
    </table>

    <!-- Footer Row -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                Recorded By: <strong style="color: #0f172a;">{{ $payment->recorder->name ?? 'Staff' }}</strong>
            </td>
            <td style="width: 50%; text-align: right; font-weight: bold; color: #047857;">
                Thank you for your payment!
            </td>
        </tr>
    </table>
</body>

</html>