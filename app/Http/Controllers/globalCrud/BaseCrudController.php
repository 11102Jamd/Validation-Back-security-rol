<?php

namespace App\Http\Controllers\globalCrud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BaseCrudController extends Controller
{
    protected $model;

    protected $validationRules = [];

    public function index()
    {
        try {
            return response()->json($this->model::OrderBy('id', 'desc')->get());
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error al obtener los registros",
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $record = $this->model::findOrFail($id);
            return response()->json($record, 201);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error: Registro no encontrado",
                "message" => $th->getMessage(),
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validationRequest($request);
            $record = $this->model::create($validatedData);
            return response()->json($record, 201);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error en la validacion de datos",
                "message" => $th->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->validationRequest($request);
            $record = $this->model::findOrFail($id);
            $record->update($validatedData);
            return response()->json($record);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error en la validacion de datos",
                "message" => $th->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $record = $this->model::findOrFail($id);
            $record->delete();
            return response()->json(["message" => "Registro eliminado exitosamente"]);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error: Registro no encontrado",
                "message" => $th->getMessage(),
            ], 404);
        }
    }

    public function validationRequest(Request $request)
    {
        if (empty($this->validationRules)) {
            throw ValidationException::withMessages(['error' => 'Reglas de validacion no definidas en el controlador hijo']);
        }
        return $request->validate($this->validationRules);
    }
}
