-- Controle de Revisoes de Veiculos
-- Consultas SQL dos relatorios exigidos no teste pratico.
-- Banco utilizado: PostgreSQL
-- Tabelas: people, vehicles e revisions
--
-- Nas consultas de revisoes, substitua :start_date e :end_date pelas datas
-- desejadas. Se um limite nao for informado, mantenha o valor NULL.

SET search_path TO narjara_areco, public;

-- ============================================================
-- A. VEICULOS
-- ============================================================

-- A.i - Todos os veiculos
SELECT
    v.id,
    v.plate,
    v.brand,
    v.model,
    v.year,
    v.color,
    p.name AS person_name
FROM vehicles AS v
JOIN people AS p ON p.id = v.person_id
ORDER BY v.plate;

-- A.ii - Todos os veiculos por pessoa, ordenados pelo nome da pessoa
SELECT
    p.name AS person_name,
    v.plate,
    v.brand,
    v.model,
    v.year,
    v.color
FROM people AS p
JOIN vehicles AS v ON v.person_id = p.id
ORDER BY p.name, v.brand, v.model;

-- A.iii - Quantidade de veiculos por genero
SELECT
    COALESCE(p.gender, 'Nao informado') AS gender,
    COUNT(v.id) AS total_vehicles
FROM people AS p
JOIN vehicles AS v ON v.person_id = p.id
GROUP BY p.gender
ORDER BY total_vehicles DESC;

-- A.iv - Marcas ordenadas pelo numero de veiculos
SELECT
    v.brand,
    COUNT(*) AS total_vehicles
FROM vehicles AS v
GROUP BY v.brand
ORDER BY total_vehicles DESC, v.brand;

-- A.v - Totais de marcas separados entre homens e mulheres
SELECT
    p.gender,
    v.brand,
    COUNT(*) AS total_vehicles
FROM people AS p
JOIN vehicles AS v ON v.person_id = p.id
WHERE p.gender IN ('Masculino', 'Feminino')
GROUP BY p.gender, v.brand
ORDER BY p.gender, total_vehicles DESC, v.brand;

-- ============================================================
-- B. PESSOAS
-- ============================================================

-- B.i - Todas as pessoas
SELECT
    p.id,
    p.name,
    p.cpf,
    p.birth_date,
    p.gender,
    p.email,
    p.phone,
    p.city,
    p.state
FROM people AS p
ORDER BY p.name;

-- B.ii - Pessoas separadas por genero, com quantidade e idade media
SELECT
    p.gender,
    COUNT(*) AS total_people,
    ROUND(
        AVG(EXTRACT(YEAR FROM AGE(CURRENT_DATE, p.birth_date)))::numeric,
        1
    ) AS average_age
FROM people AS p
WHERE p.gender IN ('Masculino', 'Feminino')
GROUP BY p.gender
ORDER BY p.gender;

-- ============================================================
-- C. REVISOES
-- ============================================================

-- C.i - Todas as revisoes dentro de um periodo
SELECT
    r.id,
    r.revision_date,
    r.mileage,
    r.description,
    r.cost,
    r.next_revision_date,
    r.maintenance_type,
    v.plate,
    v.brand,
    v.model,
    p.name AS person_name
FROM revisions AS r
JOIN vehicles AS v ON v.id = r.vehicle_id
JOIN people AS p ON p.id = v.person_id
WHERE (:start_date IS NULL OR r.revision_date >= CAST(:start_date AS date))
  AND (:end_date IS NULL OR r.revision_date <= CAST(:end_date AS date))
ORDER BY r.revision_date DESC;

-- C.ii - Marcas com maior numero de revisoes
SELECT
    v.brand,
    COUNT(r.id) AS total_revisions
FROM revisions AS r
JOIN vehicles AS v ON v.id = r.vehicle_id
WHERE (:start_date IS NULL OR r.revision_date >= CAST(:start_date AS date))
  AND (:end_date IS NULL OR r.revision_date <= CAST(:end_date AS date))
GROUP BY v.brand
ORDER BY total_revisions DESC, v.brand;

-- C.iii - Pessoas com maior numero de revisoes
SELECT
    p.id,
    p.name,
    p.gender,
    COUNT(r.id) AS total_revisions
FROM revisions AS r
JOIN vehicles AS v ON v.id = r.vehicle_id
JOIN people AS p ON p.id = v.person_id
WHERE (:start_date IS NULL OR r.revision_date >= CAST(:start_date AS date))
  AND (:end_date IS NULL OR r.revision_date <= CAST(:end_date AS date))
GROUP BY p.id, p.name, p.gender
ORDER BY total_revisions DESC, p.name;

-- C.iv - Media de tempo entre uma revisao e outra da mesma pessoa
WITH ordered_revisions AS (
    SELECT
        p.id AS person_id,
        p.name AS person_name,
        r.revision_date,
        LAG(r.revision_date) OVER (
            PARTITION BY p.id
            ORDER BY r.revision_date
        ) AS previous_revision_date
    FROM revisions AS r
    JOIN vehicles AS v ON v.id = r.vehicle_id
    JOIN people AS p ON p.id = v.person_id
    WHERE (:start_date IS NULL OR r.revision_date >= CAST(:start_date AS date))
      AND (:end_date IS NULL OR r.revision_date <= CAST(:end_date AS date))
)
SELECT
    person_id,
    person_name,
    ROUND(AVG(revision_date - previous_revision_date)::numeric, 1)
        AS average_days_between_revisions
FROM ordered_revisions
WHERE previous_revision_date IS NOT NULL
GROUP BY person_id, person_name
ORDER BY average_days_between_revisions DESC;

-- C.v - Proximas revisoes com base na media e na ultima revisao
WITH ordered_revisions AS (
    SELECT
        p.id AS person_id,
        p.name AS person_name,
        r.revision_date,
        LAG(r.revision_date) OVER (
            PARTITION BY p.id
            ORDER BY r.revision_date
        ) AS previous_revision_date,
        ROW_NUMBER() OVER (
            PARTITION BY p.id
            ORDER BY r.revision_date DESC
        ) AS revision_order
    FROM revisions AS r
    JOIN vehicles AS v ON v.id = r.vehicle_id
    JOIN people AS p ON p.id = v.person_id
),
average_intervals AS (
    SELECT
        person_id,
        person_name,
        AVG(revision_date - previous_revision_date) AS average_days
    FROM ordered_revisions
    WHERE previous_revision_date IS NOT NULL
    GROUP BY person_id, person_name
),
latest_revisions AS (
    SELECT
        person_id,
        person_name,
        revision_date AS last_revision_date
    FROM ordered_revisions
    WHERE revision_order = 1
)
SELECT
    l.person_id,
    l.person_name,
    l.last_revision_date,
    ROUND(a.average_days::numeric, 1) AS average_days,
    l.last_revision_date + ROUND(a.average_days)::integer
        AS next_revision_date
FROM latest_revisions AS l
JOIN average_intervals AS a ON a.person_id = l.person_id
ORDER BY next_revision_date;

-- ============================================================
-- CONSULTAS AUXILIARES DOS GRAFICOS DA APLICACAO
-- ============================================================

-- Pessoas por cidade
SELECT
    p.city,
    COUNT(*) AS total_people
FROM people AS p
WHERE p.city IS NOT NULL
  AND p.city <> ''
GROUP BY p.city
ORDER BY total_people DESC, p.city;

-- Veiculos por ano
SELECT
    v.year,
    COUNT(*) AS total_vehicles
FROM vehicles AS v
GROUP BY v.year
ORDER BY v.year;

-- Revisoes por mes dentro do periodo selecionado
SELECT
    DATE_TRUNC('month', r.revision_date)::date AS month,
    COUNT(*) AS total_revisions
FROM revisions AS r
WHERE (:start_date IS NULL OR r.revision_date >= CAST(:start_date AS date))
  AND (:end_date IS NULL OR r.revision_date <= CAST(:end_date AS date))
GROUP BY DATE_TRUNC('month', r.revision_date)
ORDER BY month;
