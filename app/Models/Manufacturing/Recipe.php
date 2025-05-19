<?php

namespace App\Models\Manufacturing;

use App\Models\PurchaseOrder\Input;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $table = 'recipe';

    protected $fillable = [
        'ID_manufacturing',
        'ID_inputs',
        'AmountSpent',
        'UnitMeasurement',
        'PriceQuantitySpent'
    ];

    protected $attributes = [
        'PriceQuantitySpent' => 0,
        'UnitMeasurement' => 'g'
    ];

    public function manufacturing(): BelongsTo
    {
        return $this->belongsTo(Manufacturing::class, 'ID_manufacturing');
    }

    /**
     * Laravel convierte estos metodos en propiedades dinamicas como en la linea 57
     */
    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'ID_inputs');
    }

    /**
     * Metodo privado que busca el ultio precio por referencia del insumo
     */
    private function getPriceReference()
    {
        return $this->input->inputOrders()
            ->with(['purchaseOrder' => function ($query) {
                $query->orderBy('PurchaseOrderDate', 'desc');
            }])
            ->first();
    }


    public function priceQuantitySpent()
    {
        /**
         * laravel invoca automaticamente el metodo input() y decuelve el metodo relacionado
         */
        $input = $this->input;

        if (!$input) {
            throw new \Exception("El insumo asociado no exsite");
        }

        $inputOrder = $this->getPriceReference();

        if (!$inputOrder) {
            throw new \Exception("No se encontro el insumo asociado con su orden de compra");
        }

        $amountInOrderUnit = $this->AmountSpent;

        if ($inputOrder->UnitMeasurement === 'Kg') {
            $amountInOrderUnit = $this->AmountSpent / 1000;
        } elseif ($inputOrder->UnitMeasurement === 'lb') {
            $amountInOrderUnit = $this->AmountSpent / 453.592;
        }

        return round($amountInOrderUnit * $inputOrder->UnityPrice, 2);
    }

    public function RestoreStockInputs()
    {
        $input = $this->Input;

        if ($input) {
            $input->increment('CurrentStock', $this->AmountSpent);
        }
    }

    // protected function convertToOrderUnit(float $amountInGrams, string $orderUnit): float
    // {
    //     switch ($orderUnit) {
    //         case 'Kg':
    //             return $amountInGrams / 1000;
    //         case 'lb':
    //             return $amountInGrams / 453.592;
    //         default: // gramos
    //             return $amountInGrams;
    //     }
    // }

    // $amountInOrderUnit = $this->convertToOrderUnit($this->AmountSpent, $inputOrder->UnitMeasurement);
    // esta linea anterior va en el metodo pricequantitySpent
}
