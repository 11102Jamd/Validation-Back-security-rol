<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $table = 'order_detail';

    protected $fillable = [
        'ID_order',
        'ID_product',
        'RequestedQuantity',
        'PriceQuantity'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'ID_order');
    }

    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ID_product');
    }
}
