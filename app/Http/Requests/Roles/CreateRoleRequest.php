<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'          => 'required|string|unique:roles,name',
            'permissions'   => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required'         => 'El nombre del rol es obligatorio.',
            'name.string'           => 'El nombre del rol debe ser una cadena.',
            'name.unique'           => 'El nombre del rol ya existe en la base de datos.',
            'permissions.required'  => 'Debe elegir un permiso para el rol.',
            'permissions.array'     => 'Los permisos debe ser un array.',
            'permissions.*.integer' => 'Los elementos en "permisos" deben ser números enteros.',
            'permissions.*.exists'  => 'Uno o más permisos no existen en la base de datos.',
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
