<?php

namespace App\Http\Controllers\OrderController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\globalCrud\BaseCrudController;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class OrderController extends BaseCrudController
{
    protected $model = Order::class;
    protected $validationRules = [
        'ID_users' => 'required|integer|exists:users,id',
        'OrderDate' => 'required|date',
        'OrderTotal' => 'sometimes|numeric|min:0',
        'details' => 'required|array|min:1',
        'details.*.ID_product' => 'required|exists:product,id',
        'details.*.RequestedQuantity' => 'required|integer|min:1'
    ];

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validationRequest($request);

            $order = $this->model::create([
                'ID_users' => $validatedData['ID_users'],
                'OrderDate' => $validatedData['OrderDate'],
            ]);

            $order->AddDetails($validatedData['details']);

            DB::commit();

            return response()->json([
                'Message' => "pedido creado exitosamente",
                'pedido' => $order->fresh()->load('OrderDetails')
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error en Manufacturing@Controller: " . $th->getMessage());
            return response()->json([
                'error' => 'Datos inválidos',
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('OrderDetails')->findOrFail($id);
            $order->RestoreStock();
            $order->OrderDetails()->delete();
            $order->delete();
            DB::commit();
            return response()->json([
                'message' => "Pedido eliminado exitosamente",
                'stock' => true
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => "Error al eliminar pedido",
                'error' => $th->getMessage(),
            ]);
        }
    }
}
