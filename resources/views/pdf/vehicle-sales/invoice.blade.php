<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->sale_number }}</title>
    <style>
        @page {
            size: 1069px 1472px;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 1069px;
            height: 1472px;
            position: relative;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #000000;
        }

        .bg-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 1069px;
            height: 1472px;
            z-index: 1;
        }

        .overlay-text {
            position: absolute;
            z-index: 2;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000000;
        }

        /* Top Right Serial Number Box */
        .serial-no {
            top: 290px;
            left: 680px;
            width: 220px;
            text-align: center;
            font-size: 32px;
            font-weight: 900;
            color: #000000;
            letter-spacing: 3px;
        }

        /* Date Grid */
        .date-day {
            top: 430px;
            left: 620px;
            width: 100px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .date-month {
            top: 430px;
            left: 750px;
            width: 100px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .date-year {
            top: 430px;
            left: 890px;
            width: 110px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        /* Customer Details */
        .customer-name {
            top: 403px;
            left: 110px;
            width: 430px;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .customer-address {
            top: 443px;
            left: 140px;
            width: 420px;
            font-size: 20px;
            font-weight: bold;
        }

        /* Table Line Item 1 */
        .sn-col {
            top: 560px;
            left: 45px;
            width: 50px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .qty-col {
            top: 560px;
            left: 110px;
            width: 70px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .desc-col {
            top: 560px;
            left: 230px;
            width: 500px;
            font-size: 20px;
            font-weight: bold;
            line-height: 1.3;
        }

        .description-col {
            top: 599px;
            left: 230px;
            width: 500px;
            font-size: 20px;
            font-weight: 600;
        }

        .chassis-col {
            top: 695px;
            left: 230px;
            width: 500px;
            font-size: 20px;
            font-weight: bold;
            line-height: 1.3;
        }

        .amount-col {
            top: 560px;
            left: 860px;
            width: 130px;
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }

        /* Total Box */
        .total-box {
            top: 1180px;
            left: 860px;
            width: 130px;
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }

        /* Amount in Words Line */
        .amount-words {
            top: 1242px;
            left: 220px;
            width: 780px;
            font-size: 17px;
            font-weight: bold;
            font-style: italic;
        }

        /* PAID Watermark Stamp */
        .watermark-stamp {
            position: absolute;
            top: 680px;
            left: 320px;
            z-index: 10;
            font-size: 110px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.28);
            border: 8px solid rgba(220, 38, 38, 0.28);
            padding: 10px 40px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 12px;
            transform: rotate(-25deg);
        }
    </style>
</head>

<body>
    @if(file_exists(public_path('invoice.png')))
        <img src="{{ public_path('invoice.png') }}" class="bg-template" alt="Invoice Template" />
    @endif

    @if(($sale->payment_status ?? 'unpaid') === 'paid')
        <div class="watermark-stamp">PAID</div>
    @endif

    <!-- Overlay Fields -->
    <div class="overlay-text serial-no">{{ $sale->sale_number }}</div>

    @php
        $saleDate = \Carbon\Carbon::parse($sale->sale_date);
    @endphp

    <div class="overlay-text date-day">{{ $saleDate->format('d') }}</div>
    <div class="overlay-text date-month">{{ $saleDate->format('m') }}</div>
    <div class="overlay-text date-year">{{ $saleDate->format('Y') }}</div>

    <div class="overlay-text customer-name">{{ $sale->customer_name }} ({{ $sale->customer_phone }})</div>
    <div class="overlay-text customer-address">{{ $sale->customer_address ?? 'Kano, Nigeria' }}</div>

    <!-- Line Item 1 -->
    <div class="overlay-text sn-col">1</div>
    <div class="overlay-text qty-col">1</div>
    <div class="overlay-text desc-col">
        {{ $sale->vehicle_make . ' ' . $sale->vehicle_model . ' ' . $sale->vehicle_year . ' Model' }}
        @if($sale->vehicle_color)
            ({{ $sale->vehicle_color }})
        @endif

    </div>
    @if($sale->vehicle_description)
        <div class="overlay-text description-col">{{ $sale->vehicle_description }}</div>
    @endif
    @if($sale->vehicle_vin)
        <div class="overlay-text chassis-col">CHASSIS/VIN: {{ $sale->vehicle_vin }}</div>
    @endif

    <div class="overlay-text amount-col">N{{ number_format($sale->sale_amount, 2) }}</div>

    <!-- Total -->
    <div class="overlay-text total-box">N{{ number_format($sale->sale_amount, 2) }}</div>

    <!-- Amount in words -->
    <div class="overlay-text amount-words">
        {{ $sale->amount_in_words ?? \App\Models\VehicleSale::convertAmountToWords($sale->sale_amount) }}
    </div>
</body>

</html>