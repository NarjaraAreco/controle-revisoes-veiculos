# Controle de Revisões de Veículos

Sistema web para cadastro de pessoas, veículos e revisões, com relatórios em SQL PostgreSQL, gráficos e acesso separado para administradores e clientes.

## Tecnologias

- Laravel 13 e PHP 8.3+
- Vue 3 com Inertia.js
- PostgreSQL 18
- Docker Compose/Laravel Sail
- Tailwind CSS
- Mailpit para visualizar e-mails durante o desenvolvimento

## Requisitos

- Docker Desktop em execução
- Git
- Navegador atualizado

## Instalação com Docker

No PowerShell, dentro da pasta do projeto:

```powershell
Copy-Item .env.example .env
docker compose up -d --build
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test npm install
docker compose exec laravel.test npm run build
```

Se já existir um `.env`, não o substitua. Confira se ele contém:

```dotenv
APP_URL=http://localhost:8000
APP_PORT=8000
VITE_PORT=5173
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=secret
DB_SCHEMA=narjara_areco,public
```

O serviço PostgreSQL é criado pelo `compose.yaml` com a imagem `postgres:18-alpine`. Os dados ficam no volume Docker `sail-pgsql`.

Para conferir os serviços:

```powershell
docker compose ps
```

Aplicação: <http://localhost:8000>  
Mailpit: <http://localhost:8025>

## Banco de dados

As tabelas de domínio ficam no schema PostgreSQL `narjara_areco`, que representa o nome da desenvolvedora sem acentos. O `search_path` usado pela aplicação é:

```text
narjara_areco,public
```

A migration responsável é:

```text
database/migrations/2026_08_16_210000_create_narjara_areco_schema.php
```

Ela cria o schema e move as tabelas `people`, `vehicles` e `revisions` para dentro dele. As tabelas internas do Laravel continuam acessíveis pelo schema `public`.

Para verificar o status das migrations:

```powershell
docker compose exec laravel.test php artisan migrate:status
```

Para limpar caches depois de alterar `.env` ou configurações:

```powershell
docker compose exec laravel.test php artisan optimize:clear
```

## Acesso ao sistema

### Administrador

O administrador entra pela tela `/login` usando e-mail e senha. Ele possui acesso a:

- Dashboard;
- Pessoas;
- Veículos;
- Revisões;
- Relatórios;
- Meu perfil.

### Cliente

O cliente é uma pessoa cadastrada pelo administrador. Ele escolhe a opção **Cliente** na tela de login e entra usando:

- e-mail cadastrado em Pessoas;
- data de nascimento cadastrada em Pessoas.

O cliente visualiza somente seus próprios dados, veículos, revisões, Dashboard e perfil.

## Principais rotas

| Rota | Acesso | Função |
|---|---|---|
| `/login` | Público | Login de administrador ou cliente |
| `/dashboard` | Admin/cliente | Dashboard correspondente ao tipo de acesso |
| `/people` | Administrador | CRUD de pessoas |
| `/vehicles` | Administrador | CRUD de veículos |
| `/revisions` | Administrador | CRUD de revisões |
| `/reports` | Administrador | Relatórios e gráficos |
| `/client/profile` | Cliente | Perfil somente leitura do cliente |
| `/settings/profile` | Administrador | Perfil do administrador |

## API utilizada

As rotas abaixo são protegidas e exigem autenticação de administrador.

### Cores internas

```http
GET /api/colors
```

Retorna as cores disponíveis para o cadastro de veículos:

```json
[
  { "id": "branco", "name": "Branco" },
  { "id": "preto", "name": "Preto" },
  { "id": "azul", "name": "Azul" }
]
```

### Marcas de veículos

```http
GET /api/brands
```

O backend consulta a API externa FIPE e devolve somente `id` e `name` para o frontend. A resposta é armazenada em cache por 24 horas.

Fonte externa:

```text
https://fipe.parallelum.com.br/api/v2/cars/brands
```

### Modelos de uma marca

```http
GET /api/brands/{brand}/models
```

Exemplo:

```text
GET /api/brands/59/models
```

O backend consulta:

```text
https://fipe.parallelum.com.br/api/v2/cars/brands/{brand}/models
```

Os resultados ficam em cache por 7 dias. Se a API externa falhar, o Laravel retorna o erro da requisição para que o frontend informe o problema.

## Relatórios

Os relatórios estão disponíveis em `/reports` e são acessíveis somente ao administrador. Eles incluem consultas de:

- veículos;
- pessoas;
- revisões por período;
- marcas e pessoas com mais revisões;
- intervalo médio entre revisões;
- próximas revisões previstas;
- gráficos auxiliares por cidade, ano e mês.

As consultas SQL exigidas pelo teste estão documentadas em:

```text
docs/relatorios.sql
```

O controller executa os relatórios com SQL PostgreSQL através de `DB::select()`.

## Desenvolvimento

Para executar o Vite em modo de desenvolvimento:

```powershell
docker compose exec laravel.test npm run dev -- --host=0.0.0.0
```

Para gerar os arquivos de produção:

```powershell
docker compose exec laravel.test npm run build
```

Verificações disponíveis:

```powershell
docker compose exec laravel.test npm run types:check
docker compose exec laravel.test npm run lint:check
docker compose exec laravel.test php artisan test
```

## Parar e reiniciar

Para parar os containers sem apagar os dados:

```powershell
docker compose down
```

Para iniciar novamente:

```powershell
docker compose up -d
```

Não use `docker compose down -v` durante a apresentação, pois essa opção remove o volume `sail-pgsql` e os dados cadastrados.
