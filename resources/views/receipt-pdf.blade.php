<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Official Receipt - {{ $order->order_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Serif', Georgia, 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1a1a1a;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #B6965D;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #1a1a1a;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 1px;
            color: #767676;
            margin: 0;
            text-transform: uppercase;
        }
        .receipt-badge {
            display: inline-block;
            background-color: #F3EFE7;
            border: 1px solid #B6965D;
            color: #B6965D;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .meta-col {
            width: 50%;
            vertical-align: top;
        }
        .section-header {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #B6965D;
            border-bottom: 1px solid #E2DDD5;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .meta-line {
            margin-bottom: 4px;
            font-size: 11px;
        }
        .meta-label {
            color: #666666;
            font-size: 10px;
            text-transform: uppercase;
        }
        .meta-val {
            font-weight: bold;
            color: #1a1a1a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #F8F7F2;
            border-bottom: 1px solid #B6965D;
            color: #1a1a1a;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #EAE6DF;
            padding: 8px 10px;
            font-size: 10px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .totals-table .total-row td {
            border-top: 1.5px solid #1a1a1a;
            border-bottom: 1.5px solid #1a1a1a;
            font-size: 13px;
            font-weight: bold;
            color: #1a1a1a;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .total-amount {
            color: #B6965D;
            font-weight: bold;
        }
        .footer-box {
            border-top: 1px solid #E2DDD5;
            padding-top: 15px;
            margin-top: 30px;
            font-size: 9px;
            color: #767676;
            text-align: center;
            line-height: 1.5;
        }
        .tin-badge {
            font-family: monospace;
            font-size: 9px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="brand-title">ÉCLORE</h1>
                <p class="brand-subtitle">Haute Horlogerie & Fine Jewellery Atelier</p>
                <div class="tin-badge" style="margin-top: 3px;">BIR Tax Registration TIN: 009-482-192-000</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                @php
                    $isPaid = strtolower((string) ($order->payment_status ?? '')) === 'paid';
                @endphp
                @if($isPaid)
                    <div class="receipt-badge">OFFICIAL TAX RECEIPT</div>
                    <div style="font-size: 10px; color: #767676; margin-top: 5px;">Payment Status: <strong style="color: #2e7d32;">PAID</strong></div>
                @else
                    <div class="receipt-badge" style="background-color: #F8F7F2; border-color: #888; color: #333;">OFFICIAL ORDER INVOICE</div>
                    <div style="font-size: 10px; color: #767676; margin-top: 5px;">Payment Status: <strong style="color: #b45309; text-transform: uppercase;">{{ $order->payment_status ?? 'PENDING' }}</strong></div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Order & Customer Meta -->
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-col" style="padding-right: 20px;">
                <div class="section-header">ACQUISITION DETAILS</div>
                <div class="meta-line">
                    <span class="meta-label">Order Number:</span>
                    <span class="meta-val">{{ $order->order_number }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Order Date:</span>
                    <span class="meta-val">{{ $order->created_at ? $order->created_at->format('F j, Y h:i A') : 'N/A' }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Payment Method:</span>
                    <span class="meta-val" style="text-transform: capitalize;">{{ $order->payment_method ?? 'Xendit Gateway' }}</span>
                </div>
                @if($order->tracking_number)
                <div class="meta-line">
                    <span class="meta-label">Courier Tracking:</span>
                    <span class="meta-val">{{ $order->tracking_number }} ({{ $order->courier ?? 'Armored Express' }})</span>
                </div>
                @endif
            </td>
            <td class="meta-col" style="padding-left: 20px;">
                <div class="section-header">CUSTOMER & DELIVERY DESTINATION</div>
                <div class="meta-line">
                    <span class="meta-val">{{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Email:</span>
                    <span>{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div class="meta-line">
                    <span class="meta-label">Phone:</span>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif
                <div class="meta-line" style="margin-top: 5px;">
                    <span class="meta-label">Shipping Destination:</span><br>
                    @php
                        $addressParts = [];
                        if (is_array($order->shipping_address)) {
                            $addr = $order->shipping_address;
                            $addressParts = array_filter([
                                $addr['address_line_1'] ?? $addr['street'] ?? null,
                                $addr['barangay'] ?? null,
                                $addr['city'] ?? null,
                                $addr['province'] ?? null,
                                $addr['region'] ?? null,
                                $addr['zip_code'] ?? null
                            ]);
                        } else {
                            $addressParts = array_filter([
                                $user->street,
                                $user->barangay,
                                $user->city,
                                $user->province,
                                $user->region,
                                $user->zip_code
                            ]);
                        }
                    @endphp
                    <span style="font-size: 10px;">{{ implode(', ', $addressParts) ?: 'Flagship Atelier Pickup' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 45%;">Creations & Item Description</th>
                <th style="width: 20%;">SKU / Reference</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 12%;">Unit Price</th>
                <th class="text-right" style="width: 13%;">Total (PHP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>
                    <strong style="color: #1a1a1a;">{{ $item->product_name }}</strong>
                    @if($item->product && $item->product->metal_type)
                        <div style="font-size: 9px; color: #767676;">{{ $item->product->metal_type }}</div>
                    @endif
                </td>
                <td style="font-family: monospace; font-size: 9px; color: #555;">
                    {{ $item->product_sku ?: 'ECL-ATELIER' }}
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">&#8369;{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">&#8369;{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label">Net Subtotal:</td>
            <td class="text-right">&#8369;{{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="meta-label">12% Philippine VAT:</td>
            <td class="text-right">&#8369;{{ number_format($order->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Insured Armored Delivery:</td>
            <td class="text-right">&#8369;{{ number_format($order->shipping_amount, 2) }}</td>
        </tr>
        @if($order->discount_amount > 0)
        <tr>
            <td class="meta-label" style="color: #2e7d32;">VIP Atelier Courtesy:</td>
            <td class="text-right" style="color: #2e7d32;">-&#8369;{{ number_format($order->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>{{ $isPaid ? 'TOTAL PAID:' : 'TOTAL PAYABLE:' }}</td>
            <td class="text-right total-amount">&#8369;{{ number_format($order->total_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Official Tax Notice / Footer -->
    <div class="footer-box">
        <p><strong>ÉCLORE LUXURY JEWELLERY ATELIER PHILIPPINES</strong> &bull; BIR Permit No. 2026-PH-098124</p>
        <p>This document serves as an Official Tax Invoice issued under the National Internal Revenue Code of the Philippines.</p>
        <p>All Éclore diamonds and fine gems are responsibly sourced and certified in accordance with RJC ethical standards.</p>
        <p>Concierge Client Relations: concierge@eclorejewellery.shop &bull; Tel: +63 (2) 8888-ECLORE &bull; Greenbelt 5 Flagship, Makati City</p>
    </div>

</body>
</html>
