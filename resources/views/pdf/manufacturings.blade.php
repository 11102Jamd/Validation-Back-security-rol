<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de Fabricaciones</title>
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

        .manufacturing-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .manufacturing-total {
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
        <div class="title">Reporte de Fabricaciones</div>
        <div class="period">Periodo: {{ $startDate }} al {{ $endDate }}</div>
        <div class="generated">
            Generado el: {{ $generatedAt }}
        </div>
    </div>

    @foreach ($manufacturings as $manufacturing)
        <table>
            <tr class="manufacturing-header">
                <td colspan="5">
                    Fabricación #{{ $manufacturing->id }} - Producto: {{ $manufacturing->product->ProductName }} -
                    Fecha: {{ \Carbon\Carbon::parse($manufacturing->manufacturingDate)->format('d/m/Y') }}
                </td>
            </tr>
            <tr>
                <th>Insumo</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>

            @foreach ($manufacturing->recipes as $recipe)
                <tr>
                    <td>{{ $recipe->input->InputName }}</td>
                    <td>{{ number_format($recipe->AmountSpent, 2) }}</td>
                    <td>{{ $recipe->UnitMeasurement }}</td>
                    <td>${{ number_format($recipe->PriceQuantitySpent / $recipe->AmountSpent, 4) }}</td>
                    <td>${{ number_format($recipe->PriceQuantitySpent, 2) }}</td>
                </tr>
            @endforeach

            <tr class="manufacturing-total">
                <td colspan="4">Mano de Obra ({{ $manufacturing->ManufacturingTime }} min)</td>
                <td>${{ number_format($manufacturing->Labour, 2) }}</td>
            </tr>
            <tr class="manufacturing-total">
                <td colspan="4">Total Fabricación</td>
                <td>${{ number_format($manufacturing->TotalCostProduction, 2) }}</td>
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
