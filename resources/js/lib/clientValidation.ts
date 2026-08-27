export type ClientErrors = Record<string, string>;

const mileagePattern = /^(?:\d{1,3}(?:\.\d{3})*|\d{1,10})$/;
const costPattern = /^(?:\d{1,3}(?:\.\d{3})*|\d{1,8})(?:,\d{1,2})?$/;

export function blockNonNumericKeys(event: KeyboardEvent): void {
    const allowedKeys = [
        'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp',
        'ArrowDown', 'Tab', 'Home', 'End', 'Enter',
    ];

    if (
        !event.ctrlKey
        && !event.metaKey
        && !allowedKeys.includes(event.key)
        && !/^\d$/.test(event.key)
    ) {
        event.preventDefault();
    }
}

export function blockDecimalKeys(event: KeyboardEvent): void {
    const allowedKeys = [
        'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp',
        'ArrowDown', 'Tab', 'Home', 'End', 'Enter', '.', ',',
    ];

    if (
        !event.ctrlKey
        && !event.metaKey
        && !allowedKeys.includes(event.key)
        && !/^\d$/.test(event.key)
    ) {
        event.preventDefault();
    }
}

export function sanitizeDigits(value: unknown, maxLength?: number): string {
    const cleanValue = String(value ?? '').replace(/\D/g, '');
    return maxLength ? cleanValue.slice(0, maxLength) : cleanValue;
}

export function formatCpf(value: unknown): string {
    const cpf = sanitizeDigits(value, 11);

    if (cpf.length <= 3) return cpf;
    if (cpf.length <= 6) return `${cpf.slice(0, 3)}.${cpf.slice(3)}`;
    if (cpf.length <= 9) return `${cpf.slice(0, 3)}.${cpf.slice(3, 6)}.${cpf.slice(6)}`;

    return `${cpf.slice(0, 3)}.${cpf.slice(3, 6)}.${cpf.slice(6, 9)}-${cpf.slice(9)}`;
}

export function formatPhone(value: unknown): string {
    const phone = sanitizeDigits(value, 11);

    if (phone.length <= 2) return phone ? `(${phone}` : '';
    if (phone.length <= 6) return `(${phone.slice(0, 2)}) ${phone.slice(2)}`;
    if (phone.length <= 10) {
        return `(${phone.slice(0, 2)}) ${phone.slice(2, 6)}-${phone.slice(6)}`;
    }

    return `(${phone.slice(0, 2)}) ${phone.slice(2, 7)}-${phone.slice(7)}`;
}

export function formatCep(value: unknown): string {
    const cep = sanitizeDigits(value, 8);

    return cep.length <= 5 ? cep : `${cep.slice(0, 5)}-${cep.slice(5)}`;
}

export function formatPlate(value: unknown): string {
    const plate = String(value ?? '').replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 7);

    return plate.length <= 3 ? plate : `${plate.slice(0, 3)}-${plate.slice(3)}`;
}

export function formatState(value: unknown): string {
    return String(value ?? '').replace(/[^A-Za-z]/g, '').toUpperCase().slice(0, 2);
}

export function formatMileage(value: unknown): string {
    return sanitizeDigits(value, 10).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

export function normalizeMileage(value: unknown): string {
    return sanitizeDigits(value, 10);
}

export function formatCost(value: unknown): string {
    const rawValue = String(value ?? '').replace(/[^\d.,]/g, '');
    const lastComma = rawValue.lastIndexOf(',');
    const lastDot = rawValue.lastIndexOf('.');
    const separatorIndex = lastComma >= 0 ? lastComma : lastDot;
    const hasDecimal = lastComma >= 0
        || (lastDot >= 0 && rawValue.length - lastDot - 1 <= 2);
    const integerValue = hasDecimal
        ? rawValue.slice(0, separatorIndex)
        : rawValue;
    const decimalValue = hasDecimal
        ? rawValue.slice(separatorIndex + 1).replace(/\D/g, '').slice(0, 2)
        : '';
    const integer = sanitizeDigits(integerValue, 8);

    if (!integer) {
        return '';
    }

    const formattedInteger = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return hasDecimal ? `${formattedInteger},${decimalValue}` : formattedInteger;
}

export function normalizeCost(value: unknown): string {
    const formatted = formatCost(value);
    const [integer, decimal] = formatted.split(',');

    return `${integer.replace(/\./g, '')}${decimal !== undefined ? `.${decimal}` : ''}`;
}

export function sanitizeDecimal(value: unknown, maxIntegerLength = 8): string {
    const cleanValue = String(value ?? '').replace(',', '.').replace(/[^\d.]/g, '');
    const [integerPart, ...decimalParts] = cleanValue.split('.');
    const integer = integerPart.slice(0, maxIntegerLength);
    const decimal = decimalParts.join('').slice(0, 2);

    return decimalParts.length > 0
        ? `${integer}.${decimal}`
        : integer;
}

function text(value: unknown): string {
    return String(value ?? '').trim();
}

function digits(value: unknown): string {
    return text(value).replace(/\D/g, '');
}

function validCpf(value: unknown): boolean {
    const cpf = digits(value);

    if (cpf.length !== 11 || /^([0-9])\1+$/.test(cpf)) {
        return false;
    }

    const check = (length: number): number => {
        let sum = 0;

        for (let index = 0; index < length; index += 1) {
            sum += Number(cpf[index]) * (length + 1 - index);
        }

        const remainder = (sum * 10) % 11;
        return remainder === 10 ? 0 : remainder;
    };

    return check(9) === Number(cpf[9]) && check(10) === Number(cpf[10]);
}

function validDate(value: unknown): boolean {
    const date = text(value);
    return Boolean(date) && !Number.isNaN(Date.parse(`${date}T00:00:00`));
}

export function validatePerson(value: object): ClientErrors {
    const fields = value as Record<string, unknown>;
    const errors: ClientErrors = {};
    const name = text(fields.name);
    const cpf = text(fields.cpf);
    const email = text(fields.email);
    const birthDate = text(fields.birth_date);

    if (name.length < 3) errors.name = 'Informe o nome completo (mínimo de 3 caracteres).';
    if (name.length > 255) errors.name = 'O nome deve ter no máximo 255 caracteres.';
    if (!validCpf(cpf)) errors.cpf = 'Informe um CPF válido.';
    if (!validDate(birthDate)) errors.birth_date = 'Informe uma data de nascimento válida.';
    if (birthDate && birthDate > new Date().toISOString().slice(0, 10)) {
        errors.birth_date = 'A data de nascimento não pode estar no futuro.';
    }
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) errors.email = 'Informe um e-mail válido.';
    if (email.length > 255) errors.email = 'O e-mail deve ter no máximo 255 caracteres.';

    const limits: Record<string, number> = {
        phone: 20,
        cep: 8,
        street: 255,
        number: 20,
        complement: 255,
        neighborhood: 255,
        city: 255,
    };

    Object.entries(limits).forEach(([field, limit]) => {
        const valueText = text(fields[field]);
        if (valueText.length > limit) errors[field] = `Este campo deve ter no máximo ${limit} caracteres.`;
    });

    if (text(fields.cep) && digits(fields.cep).length !== 8) errors.cep = 'O CEP deve possuir 8 dígitos.';
    if (text(fields.state) && !/^[A-Za-z]{2}$/.test(text(fields.state))) errors.state = 'Informe uma UF com 2 letras.';

    return errors;
}

export function validateVehicle(value: object, maxYear: number): ClientErrors {
    const fields = value as Record<string, unknown>;
    const errors: ClientErrors = {};
    const plate = text(fields.plate).replace(/[^A-Za-z0-9]/g, '').toUpperCase();
    const year = Number(fields.year);

    if (!text(fields.person_id)) errors.person_id = 'Selecione o proprietário.';
    if (!/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/.test(plate)) errors.plate = 'Informe uma placa brasileira válida.';
    if (!text(fields.brand) || text(fields.brand).length > 255) errors.brand = 'Informe uma marca com até 255 caracteres.';
    if (!text(fields.model) || text(fields.model).length > 255) errors.model = 'Informe um modelo com até 255 caracteres.';
    if (!Number.isInteger(year) || year < 1900 || year > maxYear) errors.year = `O ano deve estar entre 1900 e ${maxYear}.`;
    if (text(fields.color).length > 30) errors.color = 'A cor deve ter no máximo 30 caracteres.';

    return errors;
}

export function validateRevision(value: object): ClientErrors {
    const fields = value as Record<string, unknown>;
    const errors: ClientErrors = {};
    const revisionDate = text(fields.revision_date);
    const nextDate = text(fields.next_revision_date);
    const mileageText = text(fields.mileage);
    const costText = text(fields.cost);
    const mileage = Number(normalizeMileage(mileageText));
    const normalizedCost = normalizeCost(costText);
    const cost = costText === '' ? null : Number(normalizedCost);
    const description = text(fields.description);

    if (!text(fields.vehicle_id)) errors.vehicle_id = 'Selecione o veículo.';
    if (!['preventive', 'corrective'].includes(text(fields.maintenance_type))) errors.maintenance_type = 'Selecione o tipo de manutenção.';
    if (!validDate(revisionDate) || revisionDate > new Date().toISOString().slice(0, 10)) errors.revision_date = 'Informe uma data de revisão válida.';
    if (!mileagePattern.test(mileageText) || !Number.isInteger(mileage) || mileage < 0 || mileage > 4294967295) {
        errors.mileage = 'A quilometragem deve ter até 10 dígitos e estar entre 0 e 4.294.967.295.';
    }
    if (!description) errors.description = 'Informe a descrição do serviço.';
    if (description.length > 2000) errors.description = 'A descrição deve ter no máximo 2.000 caracteres.';
    if (cost !== null && (!costPattern.test(costText) || !Number.isFinite(cost) || cost < 0 || cost > 99999999.99)) {
        errors.cost = 'O custo deve estar entre 0 e 99.999.999,99.';
    }
    if (nextDate && (!validDate(nextDate) || nextDate < revisionDate)) errors.next_revision_date = 'A próxima revisão deve ser posterior à revisão atual.';

    return errors;
}

export function showClientErrors(errors: ClientErrors): void {
    if (Object.keys(errors).length > 0) {
        window.alert(`Corrija os campos antes de salvar:\n\n${Object.values(errors).join('\n')}`);
    }
}
