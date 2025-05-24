<?php

namespace Modules\Order\Http\Requests\WebService;

use Illuminate\Foundation\Http\FormRequest;

class RateOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'rating' => 'required|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [];
    }
}
