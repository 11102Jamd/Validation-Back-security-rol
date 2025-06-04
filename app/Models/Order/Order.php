<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'order';

    protected $fillable = [
        'ID_users',
        'OrderDate',
    ];

    protected $attributes = [
        'OrderTotal' => 0
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ID_users');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'ID_order');
    }

    public function addDetails(array $details)
    {
        $total = 0;

        foreach ($details as $detail) {
            $product = Product::findOrFail($detail['ID_product']);

            if ($product->CurrentStock < $detail['RequestedQuantity']) {
                throw new \Exception("Stock insuficiente para {$product->ProductName}");
            }

            $subtotal = $product->UnityPrice * $detail['RequestedQuantity'];


            $this->orderDetails()->create([
                'ID_product' => $detail['ID_product'],
                'RequestedQuantity' => $detail['RequestedQuantity'],
                'PriceQuantity' => $subtotal,
            ]);

            $product->decrement('CurrentStock', $detail['RequestedQuantity']);
            $total += $subtotal;
        }
        $this->OrderTotal = $total;
        $this->save();
        $this->refresh();
        return $this;
    }

    public function RestoreStock()
    {
        foreach ($this->OrderDetails as $detail) {
            $product = Product::find($detail->ID_product);
            if ($product) {
                $product->increment('CurrentStock', $detail->RequestedQuantity);
            }
        }
    }
}
