<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'filled|string',
            'image' => 'required|image',
            'category_id' => 'required',
            'min_stock' => 'filled|numeric|min:0',
            'per_stock' => 'filled|numeric|min:0',
            'purchase_price' => 'filled|numeric|min:0',
        ];
    }
}
