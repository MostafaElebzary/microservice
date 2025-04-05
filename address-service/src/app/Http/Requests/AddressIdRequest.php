<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressIdRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer|exists:addresses,id'
        ];
    }
}
