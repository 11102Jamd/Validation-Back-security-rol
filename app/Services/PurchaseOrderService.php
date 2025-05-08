<?php

namespace App\Services;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\Input;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function createPurchaseOrderWithInputs(array $orderData): array
    {
        return DB::transaction(function () use ($orderData) {
            // Crear la orden base
            $purchaseOrder = PurchaseOrder::create([
                'ID_supplier' => $orderData['ID_supplier'],
                'PurchaseOrderDate' => $orderData['PurchaseOrderDate']
            ]);

            $total = 0;
            $createdInputOrders = [];

            // Procesar cada input
            foreach ($orderData['inputs'] as $inputData) {
                $result = $this->processInputOrder($purchaseOrder, $inputData);
                $total += $result['subtotal'];
                $createdInputOrders[] = $result['inputOrder'];
            }

            // Actualizar el total
            $purchaseOrder->update(['PurchaseTotal' => $total]);

            return [
                'order' => $purchaseOrder->fresh()->load('inputOrders.input'),
                'input_orders' => $createdInputOrders
            ];
        });
    }

    protected function processInputOrder(PurchaseOrder $purchaseOrder, array $inputData): array
    {
        $input = Input::findOrFail($inputData['ID_input']);

        // Convertir unidades a gramos
        $grams = $input->convertUnit(
            $inputData['InitialQuantity'],
            $inputData['UnitMeasurement']
        );

        // Calcular subtotal
        $subtotal = $inputData['InitialQuantity'] * $inputData['UnityPrice'];

        // Actualizar stock
        $input->increment('CurrentStock', $grams);

        // Crear input order
        $inputOrder = $purchaseOrder->inputOrders()->create([
            'ID_input' => $input->id,
            'PriceQuantity' => $subtotal,
            'InitialQuantity' => $inputData['InitialQuantity'],
            'UnitMeasurement' => $inputData['UnitMeasurement'],
            'UnityPrice' => $inputData['UnityPrice']
        ]);

        return [
            'subtotal' => $subtotal,
            'inputOrder' => $inputOrder
        ];
    }
}
