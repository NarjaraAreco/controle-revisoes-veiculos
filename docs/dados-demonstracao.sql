-- Dados ficticios para demonstracao do Controle de Revisoes de Veiculos.
-- Execute dentro do PostgreSQL do projeto.
-- O script usa o schema personalizado da aplicacao.

BEGIN;

SET search_path TO narjara_areco, public;

-- Pessoas/clientes de demonstracao.
INSERT INTO people (
    name, cpf, birth_date, gender, phone, email,
    cep, street, number, complement, neighborhood, city, state,
    created_at, updated_at
)
VALUES
    (
        'Ana Carolina Souza', '11144477735', DATE '1992-03-14', 'Feminino',
        '67999990001', 'ana.souza@example.com',
        '79002000', 'Rua das Flores', '120', NULL, 'Centro', 'Campo Grande', 'MS',
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    ),
    (
        'Bruno Henrique Lima', '22233344405', DATE '1987-09-22', 'Masculino',
        '67999990002', 'bruno.lima@example.com',
        '79800000', 'Avenida Brasil', '450', 'Apto 12', 'Centro', 'Dourados', 'MS',
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    ),
    (
        'Carla Mendes Rocha', '33344455506', DATE '1998-11-05', 'Feminino',
        '67999990003', 'carla.rocha@example.com',
        '79300000', 'Rua Porto Alegre', '75', NULL, 'Popular Nova', 'Corumbá', 'MS',
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    ),
    (
        'Diego Alves Martins', '44455566607', DATE '1979-01-30', 'Masculino',
        '67999990004', 'diego.martins@example.com',
        '79600000', 'Rua Campo Grande', '910', NULL, 'Jardim America', 'Tres Lagoas', 'MS',
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    )
ON CONFLICT (email) DO NOTHING;

-- Veiculos vinculados as pessoas acima.
INSERT INTO vehicles (
    person_id, plate, brand, model, year, color, created_at, updated_at
)
SELECT p.id, data.plate, data.brand, data.model, data.year, data.color,
       CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    VALUES
        ('ana.souza@example.com', 'ABC1D23', 'Toyota', 'Corolla', 2022, 'Prata'),
        ('ana.souza@example.com', 'DEF2G34', 'Honda', 'Civic', 2021, 'Azul'),
        ('bruno.lima@example.com', 'GHI3J45', 'Ford', 'Ranger', 2023, 'Preto'),
        ('carla.rocha@example.com', 'JKL4M56', 'Chevrolet', 'Onix', 2020, 'Branco'),
        ('carla.rocha@example.com', 'MNO5P67', 'Volkswagen', 'T-Cross', 2022, 'Vermelho'),
        ('diego.martins@example.com', 'PQR6S78', 'Toyota', 'Hilux', 2019, 'Cinza')
) AS data(email, plate, brand, model, year, color)
JOIN people AS p ON p.email = data.email
WHERE NOT EXISTS (
    SELECT 1
    FROM vehicles AS existing
    WHERE existing.plate = data.plate
);

-- Revisoes para alimentar os relatorios, medias e previsoes.
INSERT INTO revisions (
    vehicle_id, maintenance_type, revision_date, mileage,
    description, cost, next_revision_date, created_at, updated_at
)
SELECT v.id, data.maintenance_type, data.revision_date, data.mileage,
       data.description, data.cost, data.next_revision_date,
       CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    VALUES
        ('ABC1D23', 'preventive', DATE '2025-08-10', 30000,
         'Troca de oleo e filtros', 450.00, DATE '2026-02-10'),
        ('ABC1D23', 'corrective', DATE '2026-02-10', 36000,
         'Revisao de freios e alinhamento', 780.00, DATE '2026-08-10'),
        ('ABC1D23', 'preventive', DATE '2026-07-20', 42000,
         'Revisao geral e troca de pastilhas', 920.00, DATE '2027-01-20'),
        ('DEF2G34', 'preventive', DATE '2026-06-01', 25000,
         'Troca de oleo e verificacao de pneus', 390.00, DATE '2026-12-01'),
        ('GHI3J45', 'preventive', DATE '2026-03-15', 18000,
         'Revisao preventiva do motor', 610.00, DATE '2026-09-15'),
        ('JKL4M56', 'corrective', DATE '2025-12-12', 20000,
         'Troca da bateria', 680.00, DATE '2026-06-12'),
        ('JKL4M56', 'preventive', DATE '2026-06-12', 25000,
         'Troca de oleo e filtros', 430.00, DATE '2026-12-12'),
        ('MNO5P67', 'preventive', DATE '2026-07-01', 15000,
         'Revisao de suspensao', 550.00, DATE '2027-01-01'),
        ('PQR6S78', 'corrective', DATE '2026-05-20', 85000,
         'Reparo no sistema de arrefecimento', 1150.00, DATE '2026-11-20')
) AS data(
    plate, maintenance_type, revision_date, mileage,
    description, cost, next_revision_date
)
JOIN vehicles AS v ON v.plate = data.plate
WHERE NOT EXISTS (
    SELECT 1
    FROM revisions AS existing
    WHERE existing.vehicle_id = v.id
      AND existing.revision_date = data.revision_date
      AND existing.description = data.description
);

COMMIT;

-- Conferencia rapida dos dados inseridos.
SELECT 'people' AS tabela, COUNT(*) AS total FROM people
UNION ALL
SELECT 'vehicles', COUNT(*) FROM vehicles
UNION ALL
SELECT 'revisions', COUNT(*) FROM revisions;
