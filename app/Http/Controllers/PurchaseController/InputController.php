<?php

namespace App\Http\Controllers\PurchaseController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\globalCrud\BaseCrudController;
use App\Models\PurchaseOrder\Input;
use Illuminate\Http\Request;

class InputController extends BaseCrudController
{
    protected $model = Input::class;
    protected $validationRules = [
        'InputName'=>'required|string|max:50',
    ];

    public function index(){
        $inputs = Input::with(['inputOrders' => function($query){
            $query->latest()->get()->take(1);
        }])->OrderBy('id','desc')->get();

        return response()->json($inputs);
    }
}
