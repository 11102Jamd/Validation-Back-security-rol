<?php

namespace App\Http\Controllers\PurchaseController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\globalCrud\BaseCrudController;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PurchaseOrderController extends BaseCrudController
{
    protected $model = PurchaseOrder::class;
    protected $validationRules = [
        'ID_supplier' => 'required|exists:supplier,id',
        'PurchaseOrderDate' => 'required|date',
        'inputs' => 'required|array|min:1',
        'inputs.*.ID_input' => 'required|exists:inputs,id',
        'inputs.*.InitialQuantity' => 'required|numeric|min:0',
        'inputs.*.UnitMeasurement' => 'required|string|in:g,Kg,lb',
        'inputs.*.UnityPrice' => 'required|numeric|min:0'
    ];

    public function __construct(
        private PurchaseOrderService $purchaseOrderService
    ){}

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validationRequest($request);

            $result = $this->purchaseOrderService->createPurchaseOrderWithInputs($validatedData);

            DB::commit();

            return response()->json([
                'Message' => "Orden de compra creada exitosamente",
                'OrdenCompra' => $result['order'],
                'Insumos' => $result['input_orders']
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error en PurchaseOrder@Controller: " . $th->getMessage());
            return response()->json([
                'error' => 'Datos inválidos',
                'message' => $th->getMessage(),
            ], 422);
        }
    }
}
