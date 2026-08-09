<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;


class ValidCpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', (string) $value);

        if (
            strlen($cpf) !== 11 ||
            preg_match('/^(\d)\1{10}$/', $cpf)
        ) {
            $fail('O CPF informado é inválido.');

            return;
        }

        for ($position = 9; $position <= 10; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }

            $remainder = ($sum * 10) % 11;
            $digit = $remainder === 10 ? 0 : $remainder;

            if ($digit !== (int) $cpf[$position]) {
                $fail('O CPF informado é inválido.');

                return;
            }
        }
    }
}
