<?php

namespace App\Http\Requests\FakeStore;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'userId' => ['required', 'integer', 'min:1'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'min:1'],
            'products.*.title' => ['required', 'string', 'max:255'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.description' => ['required', 'string'],
            'products.*.category' => ['required', 'string', 'max:255'],
            'products.*.image' => ['required', 'url', 'max:2048'],
            'products.*.quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
