<?php

namespace App\Http\Controllers\OrderController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\globalCrud\BaseCrudController;
use App\Models\Order\Product;
use Illuminate\Http\Request;

class ProductController extends BaseCrudController
{
    protected $model = Product::class;
    protected $validationRules = [
        'ProductName' => 'required|string|max:60',
        'InitialQuantity' => 'required|integer|min:1',
        'UnityPrice' => 'required|numeric|min:0',
    ];


    public  function store(Request $request)
    {
        try {
            $validatedData = $this->validationRequest($request);

            $validatedData['CurrentStock'] = $validatedData['InitialQuantity'];

            $product = $this->model::create($validatedData);

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'data' => $product
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Error al crear el producto',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->validationRequest($request);

            $product = $this->model::findOrFail($id);

            if (isset($validatedData["InitialQuantity"]) && $validatedData["InitialQuantity"] != $product->InitialQuantity) {
                $validatedData["CurrentStock"] = $validatedData['InitialQuantity'];
            }

            $product->update($validatedData);

            return response()->json([
                "message" => "Producto actualizado exitosamente",
                "data" => $product,
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "error al actualizar el producto",
                "message" => $th->getMessage(),
            ], 500);
        }
    }
}
