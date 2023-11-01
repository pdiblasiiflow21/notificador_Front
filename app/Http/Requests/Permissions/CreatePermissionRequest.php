<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePermissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:permissions,name',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.string'   => 'El nombre del permiso debe ser un texto.',
            'name.unique'   => 'El nombre del permiso ya existe en la base de datos.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
