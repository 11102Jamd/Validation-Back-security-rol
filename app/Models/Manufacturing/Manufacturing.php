<?php

namespace App\Models\Manufacturing;

use App\Models\Order\Product;
use App\Models\PurchaseOrder\Input;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturing extends Model
{
    protected $table = 'manufacturing';

    protected $fillable = [
        'ID_product',
        'ManufacturingTime',
        'Labour',
        'ManufactureProductG',
        'TotalCostProduction',
    ];

    protected $attributes = [
        'Labour' => 0,
        'ManufactureProductG' => 0,
        'TotalCostProduction' => 0
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ID_product');
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'ID_manufacturing');
    }

    public function calculateLabour()
    {
        if ($this->ManufacturingTime != 0 && $this->ManufacturingTime > 0) {
            $timeInHours = $this->ManufacturingTime / 60;
            $this->Labour = $timeInHours * 10000;
            $this->save();
        }

        return $this;
    }

    public function addIngredients(array $recipes)
    {
        $total = 0;
        $totalGrams = 0;
        foreach ($recipes as $recipe) {
            $input = Input::findOrFail($recipe['ID_inputs']);

            $amountInGrams = $recipe['AmountSpent'];

            if ($input->CurrentStock < $recipe['AmountSpent']) {
                throw new \Exception("Stock insuficiente para {$input->InputName}");
            }

            if (($input->CurrentStock - $recipe['AmountSpent']) <= 200) {
                throw new \Exception("StocK Minimo: ".$input->InputName);
            }

            $recipeModel = $this->recipes()->create([
                'ID_inputs' => $input->id,
                'AmountSpent' => $amountInGrams,
                'UnitMeasurement' => 'g'
            ]);

            $subtotal = $recipeModel->priceQuantitySpent();
            $totalGrams += $amountInGrams;

            $recipeModel->update(['PriceQuantitySpent' => $subtotal]);

            $input->decrement('CurrentStock', $amountInGrams);
            $total += $subtotal;
        }
        $this->TotalCostProduction = $total + $this->Labour;
        $this->ManufactureProductG = $totalGrams;
        $this->save();
        return $this->fresh()->load('recipes.Input');
    }
}
