<?php

namespace App\Models\Order;

use App\Models\Manufacturing\Manufacturing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'product';

    protected $fillable = [
        'ProductName',
        'InitialQuantity',
        'CurrentStock',
        'UnityPrice'
    ];

    public function orderDetails() : HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function manufacturings() : HasMany
    {
        return $this->hasMany(Manufacturing::class);
    }
}
