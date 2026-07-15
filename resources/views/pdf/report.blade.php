<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Laporan Penjualan</title>

    <style>

        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size:13px;
            color:#222;
        }

        h1{
            text-align:center;
            margin-bottom:0;
        }

        h3{
            text-align:center;
            margin-top:5px;
            color:#555;
        }

        .info{
            margin-top:30px;
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th{
            background:#2563eb;
            color:white;
            padding:10px;
            border:1px solid #ccc;
        }

        table td{
            padding:8px;
            border:1px solid #ccc;
        }

        .text-right{
            text-align:right;
        }

        .footer{
            margin-top:30px;
            text-align:right;
            font-weight:bold;
            font-size:15px;
        }

    </style>

</head>

<body>

<h1>Bengkel Motor</h1>

<h3>Laporan Penjualan</h3>

<div class="info">

    <strong>Tanggal Cetak :</strong>

    {{ now()->format('d F Y H:i') }}

</div>

<table>

    <thead>

        <tr>

            <th>No</th>

            <th>Order</th>

            <th>Customer</th>

            <th>Tanggal</th>

            <th>Total</th>

            <th>Status</th>

        </tr>

    </thead>

    <tbody>

    @php
        $grandTotal = 0;
    @endphp

    @forelse($orders as $order)

        @php
            $grandTotal += $order->total_amount;
        @endphp

        <tr>

            <td>{{ $loop->iteration }}</td>

            <td>#{{ $order->id }}</td>

            <td>{{ $order->user->name }}</td>

            <td>{{ $order->created_at->format('d-m-Y') }}</td>

            <td class="text-right">
                Rp {{ number_format($order->total_amount,0,',','.') }}
            </td>

            <td>{{ ucfirst($order->status) }}</td>

        </tr>

    @empty

        <tr>

            <td colspan="6" style="text-align:center">

                Belum ada data transaksi

            </td>

        </tr>

    @endforelse

    </tbody>

</table>

<div class="footer">

    Total Pendapatan :
    Rp {{ number_format($grandTotal,0,',','.') }}

</div>

</body>

</html>