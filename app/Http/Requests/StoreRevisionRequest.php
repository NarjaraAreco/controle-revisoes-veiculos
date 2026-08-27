<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],

            'maintenance_type' => [
                'required',
                'in:preventive,corrective',
            ],

            'revision_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'mileage' => [
                'required',
                'integer',
                'regex:/^\d{1,10}$/',
                'min:0',
                'max:4294967295',
            ],

            'description' => [
                'required',
                'string',
                'max:2000',
            ],

            'cost' => [
                'nullable',
                'numeric',
                'regex:/^\d{1,8}(\.\d{1,2})?$/',
                'min:0',
                'max:99999999.99',
            ],

            'next_revision_date' => [
                'nullable',
                'date',
                'after_or_equal:revision_date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Selecione o veículo.',
            'vehicle_id.exists' => 'O veículo selecionado não existe.',

            'maintenance_type.required' => 'Selecione o tipo de manutenção.',
            'maintenance_type.in' => 'O tipo de manutenção selecionado é inválido.',

            'revision_date.required' => 'Informe a data da revisão.',
            'revision_date.before_or_equal' =>
                'A data da revisão não pode ser futura.',

            'mileage.required' => 'Informe a quilometragem.',
            'mileage.integer' => 'A quilometragem deve ser um número inteiro.',
            'mileage.regex' => 'A quilometragem deve possuir no máximo 10 dígitos.',
            'mileage.min' => 'A quilometragem não pode ser negativa.',

            'description.required' => 'Informe a descrição da revisão.',

            'cost.numeric' => 'O custo deve ser um valor numérico.',
            'cost.regex' => 'O custo deve possuir até 8 dígitos e 2 casas decimais.',
            'cost.min' => 'O custo não pode ser negativo.',

            'next_revision_date.after_or_equal' =>
                'A próxima revisão deve ser igual ou posterior à revisão atual.',
        ];
    }
}
