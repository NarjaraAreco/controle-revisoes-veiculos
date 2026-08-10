<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'plate' => strtoupper(
                preg_replace(
                    '/[^A-Za-z0-9]/',
                    '',
                    (string) $this->input('plate')
                )
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'person_id' => [
                'required',
                'integer',
                'exists:people,id',
            ],

            'plate' => [
                'required',
                'string',
                'size:7',
                'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
                Rule::unique('vehicles', 'plate')->ignore($this->route('vehicle')),
            ],

            'brand' => [
                'required',
                'string',
                'max:255',
            ],

            'model' => [
                'required',
                'string',
                'max:255',
            ],

            'year' => [
                'required',
                'integer',
                'between:1900,' . (date('Y') + 1),
            ],

            'color' => [
                'nullable',
                'string',
                'max:30',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'person_id.required' => 'Selecione o proprietário.',
            'person_id.exists' => 'O proprietário selecionado não existe.',

            'plate.required' => 'Informe a placa.',
            'plate.size' => 'A placa deve possuir 7 caracteres.',
            'plate.regex' => 'Informe uma placa válida.',
            'plate.unique' => 'Esta placa já está cadastrada.',

            'brand.required' => 'Informe a marca.',
            'model.required' => 'Informe o modelo.',
            'year.required' => 'Informe o ano.',
            'year.between' => 'Informe um ano válido.',

            'color.max' => 'A cor não pode ultrapassar 30 caracteres.',
        ];
    }
}