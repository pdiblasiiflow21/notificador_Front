<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => 'required|string|unique:users,name',
            'email'    => 'required|string|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|exists:roles,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required'     => 'El nombre del usuario es obligatorio.',
            'name.string'       => 'El nombre del usuario debe ser una cadena.',
            'name.unique'       => 'El nombre del usuario ya existe en la base de datos.',
            'email.required'    => 'La dirección de correo electrónico es obligatoria.',
            'email.string'      => 'La dirección de correo electrónico debe ser una cadena.',
            'email.unique'      => 'La dirección de correo electrónico ya existe en la base de datos.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string'   => 'La contraseña debe ser una cadena.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required'     => 'Seleccione un rol para el usuario.',
            'role.exists'       => 'No se ha encontrado el rol asociado a este usuario.',
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
