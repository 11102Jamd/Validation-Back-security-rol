<?php

namespace App\Http\Controllers\globalCrud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


/**
 * Controlador base para operaciones CRUD genéricas.
 *
 * Este controlador define métodos comunes para interactuar con modelos de Eloquent:
 * - Listar registros (index)
 * - Mostrar un registro (show)
 * - Crear un registro (store)
 * - Actualizar un registro (update)
 * - Eliminar un registro (destroy)
 *
 * Los controladores hijos deben:
 * - Definir la propiedad `$model` con el modelo a utilizar.
 * - Especificar reglas de validación en `$validationRules`.
 */
class BaseCrudController extends Controller
{

    /**
     * Modelo asociado al controlador.
     *
     * @var string
     */
    protected $model;


    /**
     * Reglas de validación para las operaciones CRUD.
     *
     * @var array<string, string>
     */
    protected $validationRules = [];

    /**
     * Obtiene todos los registros en orden descendente por su identificador
     * devolviendo una respuesta exitosa o error en JSON.
     *
     * @return \Illuminate\Http\JsonResponse Lista de registros o mensaje de error.
     */
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


    /**
     * Obtiene un registro específico por su identificador único.
     *
     * @param int $id Identificador único del registro.
     * @return \Illuminate\Http\JsonResponse Registro encontrado o mensaje de error.
     */
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


    /**
     * Crea un nuevo registro en la base de datos.
     *
     * Pasos importantes:
     * - Valida los datos recibidos en la solicitud Http.
     * - Crea el registro en el modelo asociado.
     * - Devuelve el registro creado en formato JSON.
     *
     * @param \Illuminate\Http\Request $request Datos a almacenar.
     * @return \Illuminate\Http\JsonResponse Registro creado o error de validación.
     */
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

    /**
     * Actualiza un registro existente.
     *
     * Pasos importantes:
     * - Valida los datos recibidos en la solicitud Http.
     * - Busca el registro por ID.
     * - Actualiza sus campos.
     * - Devuelve el registro actualizado.
     *
     * @param \Illuminate\Http\Request $request Datos a actualizar.
     * @param int $id ID del registro a modificar.
     * @return \Illuminate\Http\JsonResponse Registro actualizado o error de validación.
     */
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

    /**
     * Elimina un registro de la base de datos
     *
     * Recibe el identificador unico del registro a eliminar. Valida si el registro existe, lo elimina
     * y devuelve una respuesta JSON con un mensaje de exito
     *
     * @param int $id identificador unico del registro a eliminar
     * @return \Illuminate\Request\JsonRespnse Respuesta de exito o error en formato JSON
     */
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


    /**
     * Valida los datos de la solicitud según las reglas definidas.
     *
     * Verifica que existan reglas de validación en el controlador hijo y aplica dichas reglas
     * a los datos de la solicitud. Si no existen reglas, lanza una excepción de validación.
     *
     * @param \Illuminate\Http\Request $request Objeto con los datos de la solicitud.
     * @return array Datos validados según las reglas definidas.
     *
     * @throws \Illuminate\Validation\ValidationException Si no existen reglas de validación o si la validación falla.
     */
    public function validationRequest(Request $request)
    {
        if (empty($this->validationRules)) {
            throw ValidationException::withMessages(['error' => 'Reglas de validacion no definidas en el controlador hijo']);
        }
        return $request->validate($this->validationRules);
    }
}
