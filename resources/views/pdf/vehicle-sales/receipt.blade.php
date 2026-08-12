<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->sale_number }}</title>
    <style>
        @page {
            size: 1498px 1050px;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 1498px;
            height: 1050px;
            position: relative;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #000000;
        }

        .bg-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 1498px;
            height: 1050px;
            z-index: 1;
        }

        .overlay-text {
            position: absolute;
            z-index: 2;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000000;
        }

        /* Receipt Serial Number Box */
        .receipt-no {
            top: 320px;
            left: 1020px;
            width: 200px;
            text-align: center;
            font-size: 34px;
            font-weight: 900;
            color: #111111;
            letter-spacing: 3px;
        }

        /* Date Line */
        .receipt-date {
            top: 430px;
            left: 1155px;
            width: 310px;
            font-size: 19px;
            font-weight: bold;
        }

        /* Received From Line */
        .received-from {
            top: 485px;
            left: 365px;
            width: 1070px;
            font-size: 21px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Being Payment For Line */
        .being-payment {
            top: 565px;
            left: 400px;
            width: 1040px;
            font-size: 19px;
            font-weight: bold;
        }

        /* The Sum Of (Amount in Words) Line */
        .sum-of-words {
            top: 690px;
            left: 300px;
            width: 1120px;
            font-size: 19px;
            font-weight: bold;
            font-style: italic;
        }

        /* Naira Box */
        .naira-val {
            top: 866px;
            left: 120px;
            width: 480px;
            font-size: 34px;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>

<body>
    @if(file_exists(public_path('receipt.png')))
        <img src="{{ public_path('receipt.png') }}" class="bg-template" alt="Receipt Template" />
    @endif

    <!-- Overlay Fields -->
    <div class="overlay-text receipt-no">{{ $sale->sale_number }}</div>

    <div class="overlay-text receipt-date">{{ \Carbon\Carbon::parse($sale->sale_date)->format('jS F, Y') }}</div>

    <div class="overlay-text received-from">{{ $sale->customer_name }}</div>

    <div class="overlay-text being-payment">
        One unit
        {{ trim(($sale->vehicle_year ? $sale->vehicle_year . ' ' : '') . $sale->vehicle_make . ' ' . $sale->vehicle_model) }}
        @if($sale->vehicle_vin)
            (VIN: {{ $sale->vehicle_vin }})
        @endif
    </div>

    <div class="overlay-text sum-of-words">
        {{ \App\Models\VehicleSale::convertAmountToWords($sale->amount_paid) }}
    </div>

    <div class="overlay-text naira-val">N{{number_format($sale->amount_paid, 2)}}</div>
</body>

</html>