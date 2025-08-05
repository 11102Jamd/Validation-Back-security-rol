<?php

namespace App\Http\Controllers\ManufacturingController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\globalCrud\BaseCrudController;
use App\Models\Manufacturing\Manufacturing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManufacturingController extends BaseCrudController
{
    protected $model = Manufacturing::class;
    protected $validationRules = [
        'ID_product' => 'required|exists:product,id',
        'manufacturingDate' => 'required|date',
        'ManufacturingTime' => 'required|integer|min:1',
        'recipes' => 'required|array|min:1',
        'recipes.*.ID_inputs' => 'required|exists:inputs,id',
        'recipes.*.AmountSpent' => 'required|numeric|min:0.01',
        'recipes.*.UnitMeasurement' => 'required|string|in:g,kg,lb'
    ];

    public function show($id)
    {
        try {
            $record = $this->model::with(["product","recipes.input"])->findOrFail($id);
            return response()->json($record);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 404,
                "message" => $th->getMessage()
            ], 404);
        }
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validationRequest($request);

            $manufacturing = $this->model::create([
                'ID_product' => $validatedData['ID_product'],
                'manufacturingDate' => $validatedData['manufacturingDate'],
                'ManufacturingTime' => $validatedData['ManufacturingTime']
            ]);

            $manufacturing->CalculateLabour();

            $manufacturing->addIngredients($validatedData['recipes']);

            DB::commit();

            return response()->json([
                'Message' => "fabricacion creado exitosamente",
                'Fabricacion' => $manufacturing->fresh()->load('recipes')
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
            $manufacturing = Manufacturing::with('recipes')->findOrFail($id);

            foreach ($manufacturing->recipes as $recipe) {
                $recipe->RestoreStockInputs();
            }

            $manufacturing->recipes()->delete();
            $manufacturing->delete();

            DB::commit();
            return response()->json([
                'message' => "Fabricación eliminada exitosamente",
                'stock' => true
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => "Error al eliminar fabricación",
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
