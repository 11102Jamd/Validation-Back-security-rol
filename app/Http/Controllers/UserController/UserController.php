<?php

namespace App\Http\Controllers\UserController;

use App\Http\Controllers\Controller;
use App\Http\Controllers\globalCrud\BaseCrudController;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseCrudController
{
    protected $model = User::class;

    protected $validationRules = [
        'name1' => 'required|string|max:50',
        'name2' => 'required|string|max:50',
        'surname1' => 'required|string|max:50',
        'surname2' => 'required|string|max:50',
        'email' => 'required|email|unique:users,email',
        'password' => 'nullable|string|min:8',
        'rol' => 'required|string'
    ];

    public function update(Request $request, $id)
    {
        $this->validationRules['email'] = 'required|email|unique:users,email,'.$id;
        return parent::update($request, $id);
    }
}
