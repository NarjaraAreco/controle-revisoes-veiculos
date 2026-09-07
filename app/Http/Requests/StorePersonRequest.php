<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidCpf;
use Illuminate\Validation\Rule;

class StorePersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => preg_replace('/\D/', '', $this->cpf ?? ''),
            'cep' => preg_replace('/\D/', '', $this->cep ?? ''),
            'state' => strtoupper($this->state ?? ''),
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {

        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                new ValidCpf(),
                Rule::unique('people', 'cpf')->ignore($this->route('person')),
            ],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'string', 'in:Feminino,Masculino,Outro'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('people', 'email')->ignore($this->route('person')),
            ],
            'cep' => ['nullable', 'digits:8'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome completo: informe o nome completo.',
            'name.string' => 'Nome completo: informe um texto válido.',
            'name.max' => 'Nome completo: não pode ultrapassar 255 caracteres.',

            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'birth_date.required' => 'Data de nascimento: informe a data.',
            'birth_date.date' => 'Data de nascimento: informe uma data válida.',
            'birth_date.before_or_equal' => 'Data de nascimento: não pode ser futura.',

            'gender.string' => 'Sexo: informe um valor válido.',
            'gender.in' => 'Sexo: selecione uma opção válida.',

            'phone.string' => 'Telefone: informe um valor válido.',
            'phone.max' => 'Telefone: não pode ultrapassar 20 caracteres.',

            'email.required' => 'E-mail: informe o endereço de e-mail.',
            'email.email' => 'E-mail: informe um endereço válido.',
            'email.max' => 'E-mail: não pode ultrapassar 255 caracteres.',
            'email.unique' => 'E-mail: este endereço já está cadastrado.',

            'cep.digits' => 'CEP: informe exatamente 8 dígitos.',
            'street.string' => 'Rua: informe um texto válido.',
            'street.max' => 'Rua: não pode ultrapassar 255 caracteres.',
            'number.string' => 'Número: informe um texto válido.',
            'number.max' => 'Número: não pode ultrapassar 20 caracteres.',
            'complement.string' => 'Complemento: informe um texto válido.',
            'complement.max' => 'Complemento: não pode ultrapassar 255 caracteres.',
            'neighborhood.string' => 'Bairro: informe um texto válido.',
            'neighborhood.max' => 'Bairro: não pode ultrapassar 255 caracteres.',
            'city.string' => 'Cidade: informe um texto válido.',
            'city.max' => 'Cidade: não pode ultrapassar 255 caracteres.',
            'state.string' => 'Estado: informe uma UF válida.',
            'state.size' => 'Estado: informe exatamente 2 letras.',
        ];
    }
}
