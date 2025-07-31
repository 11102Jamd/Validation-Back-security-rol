<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de Órdenes de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .period {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .generated {
            font-size: 12px;
            color: #555;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .order-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .order-total {
            text-align: right;
            font-weight: bold;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Reporte de Órdenes de Compra</div>
        <div class="period">Periodo: {{ isset($startDate) ? $startDate : 'N/A' }} al
            {{ isset($endDate) ? $endDate : 'N/A' }}</div>
        <div class="generated">
            Generado el: {{ $generatedAt ?? 'Fecha no disponible' }}
        </div>
    </div>

    @foreach ($purchaseOrders as $order)
        <table>
            <tr class="order-header">
                <td colspan="4">
                    Orden #{{ $order->id }} - Proveedor:
                    {{ $order->supplier ? $order->supplier->name : 'Sin proveedor' }} -
                    Fecha: {{ \Carbon\Carbon::parse($order->PurchaseOrderDate)->format('d/m/Y') }}
                </td>
            </tr>
            <tr>
                <th>Insumo</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>

            @foreach ($order->inputOrders as $inputOrder)
                <tr>
                    <td>{{ $inputOrder->input->InputName }}</td>
                    <td>{{ $inputOrder->InitialQuantity }} {{ $inputOrder->UnitMeasurement }}</td>
                    <td>${{ number_format($inputOrder->UnityPrice, 2) }}</td>
                    <td>${{ number_format($inputOrder->PriceQuantity, 2) }}</td>
                </tr>
            @endforeach

            <tr class="order-total">
                <td colspan="3">Total Orden</td>
                <td>${{ number_format($order->PurchaseTotal, 2) }}</td>
            </tr>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="grand-total">
        Total General: ${{ number_format($totalAmount, 2) }}
    </div>
</body>

</html>
