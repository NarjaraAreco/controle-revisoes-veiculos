# Controle de Revisões de Veículos — Contexto e Backup

Última atualização: 09/08/2026

Este arquivo guarda o contexto do projeto para retomada caso a conversa seja fechada.

## Objetivo

Criar um sistema web de controle de revisões de veículos para o teste prático de programação júnior.

## Tecnologias exigidas pelo teste

- PHP 8.1 ou superior com Laravel;
- PostgreSQL 17 ou superior;
- Vue;
- HTML, CSS e JavaScript;
- Docker/container para apresentação;
- Pelo menos uma API consumida e documentada;
- CRUDs, relatórios, SQLs e gráficos.

## O que já foi feito

### Projeto e versionamento

- Projeto Laravel com Vue/Inertia iniciado.
- Repositório Git criado.
- Branch principal configurada como `main`.
- Projeto enviado ao GitHub.
- Commits existentes:
  - configuração inicial do projeto Laravel;
  - criação das entidades de pessoas, veículos e revisões;
  - implementação do cadastro de pessoas com validações.

### Docker e banco

- `compose.yaml` configurado com:
  - aplicação Laravel (`laravel.test`);
  - PostgreSQL 18;
  - Mailpit;
  - rede e volume do Sail.
- O Docker já foi utilizado com sucesso.
- As migrations foram executadas dentro do container.
- A migration de revisões já foi executada.
- O ambiente atual usa PostgreSQL dentro do Docker.
- O plano inicial era desenvolver com SQLite e depois migrar para PostgreSQL. A configuração final atual já está voltada para PostgreSQL.
- `.env.example` ainda usa SQLite como exemplo padrão e deverá ser alinhado/documentado antes da entrega.
- Não registrar neste arquivo valores de senha, `APP_KEY` ou outros segredos do `.env`.

### Banco e entidades

Já existem migrations, models ou estrutura inicial para:

- usuários;
- pessoas/proprietários;
- veículos;
- revisões;
- cache, jobs, sessões e autenticação do Laravel.

Relacionamentos atuais:

- uma pessoa possui vários veículos;
- um veículo pertence a uma pessoa;
- um veículo possui várias revisões;
- uma revisão pertence a um veículo.

### Pessoas

Já implementado:

- listagem de pessoas;
- tela de criação;
- validação de CPF;
- validação dos campos;
- consulta de CEP pela ViaCEP;
- cadastro no banco;
- mensagens de erro no formulário.

### Veículos

Já implementado:

- migration da tabela de veículos;
- model `Vehicle`;
- relacionamento com pessoas e revisões;
- listagem de veículos;
- controller com `index()`;
- controller com `create()` carregando os proprietários;
- tela `resources/js/pages/vehicles/Create.vue`;
- formulário com proprietário, placa, marca, modelo, ano e cor;
- API de cores em `/api/colors`;
- campo de cor carregado pela API;
- estrutura visual de cancelamento e salvamento.

## Ponto exato onde paramos

Estávamos finalizando o cadastro de veículos.

O arquivo `resources/js/pages/vehicles/Create.vue` já envia o formulário para:

```
POST /vehicles
```

Porém, ainda falta:

1. criar a rota `POST /vehicles`;
2. criar `StoreVehicleRequest`;
3. implementar o método `store()` em `VehicleController`;
4. validar e normalizar a placa;
5. salvar o veículo no banco;
6. redirecionar para a lista com mensagem de sucesso;
7. testar o cadastro dentro do Docker.

## API existente

Foi criada uma rota simples:

```
GET /api/colors
```

Ela retorna cores para o formulário de veículos. Antes da apresentação, essa API deverá ser documentada no README ou em uma seção própria de documentação.

## Pendências principais do teste

### CRUD de veículos

- `store`;
- edição;
- atualização;
- exclusão;
- links e ações na listagem;
- chamada do cadastro de veículos a partir da área de pessoas, conforme o enunciado.

### CRUD de revisões

- controller;
- request de validação;
- rotas;
- listagem;
- criação;
- edição;
- atualização;
- exclusão;
- formulário ligado ao veículo;
- cálculo ou preenchimento da próxima revisão.

### Schema do PostgreSQL

O teste pede um schema próprio com o nome do desenvolvedor. Atualmente o PostgreSQL ainda usa o schema `public`:

- criar o schema definido para o projeto;
- ajustar o `search_path` do Laravel;
- garantir que migrations e consultas usem o schema correto;
- validar isso dentro do Docker.

### Relatórios e gráficos

Criar relatórios visuais e SQLs salvos em arquivo. O PDF pede relatórios de:

#### Veículos

- todos os veículos;
- veículos por pessoa, ordenados pelo nome;
- quem possui mais veículos, comparando homens e mulheres;
- marcas ordenadas pela quantidade de veículos;
- totais de marcas separados entre homens e mulheres.

#### Pessoas

- todas as pessoas;
- pessoas separadas por homens e mulheres;
- idade média de homens e mulheres.

#### Revisões

- revisões dentro de um período;
- marcas com maior número de revisões;
- pessoas com maior número de revisões;
- média de tempo entre revisões de uma mesma pessoa;
- próximas revisões com base no tempo médio e na última revisão.

Todos os relatórios devem ter apresentação visual e gráficos.

### Documentação, qualidade e entrega

- criar README com instalação e execução;
- documentar Docker/Sail;
- documentar a API;
- documentar rotas e fluxo de dados;
- salvar os SQLs dos relatórios;
- criar testes dos cadastros e regras importantes;
- revisar responsividade e usabilidade;
- revisar mensagens de erro, exceções e logs;
- executar build e testes dentro do container;
- preparar roteiro de apresentação.

## Abas futuras solicitadas

### Clientes

Será criada mais adiante uma aba própria para clientes.

A decisão planejada é não misturar automaticamente clientes com proprietários de veículos. Primeiro será confirmado se cliente e proprietário representam a mesma pessoa ou entidades diferentes.

### Usuários/Colaboradores

O projeto já possui a tabela `users` e autenticação do Laravel.

Mais adiante será criada uma aba de usuários/colaboradores com possibilidade de:

- listar colaboradores;
- cadastrar e editar usuários;
- ativar ou desativar usuários;
- definir perfil ou nível de acesso;
- separar administrador e operador, se necessário.

Deve-se aproveitar a tabela `users` existente, evitando criar uma segunda tabela de autenticação sem necessidade.

## Ordem planejada para continuar

1. Finalizar o cadastro e CRUD de veículos.
2. Criar o CRUD de revisões.
3. Validar o fluxo completo pessoa → veículo → revisão.
4. Ajustar schema e configuração final do PostgreSQL.
5. Criar relatórios, gráficos e arquivos SQL.
6. Documentar a API e o projeto.
7. Criar a aba Clientes.
8. Criar a aba Usuários/Colaboradores.
9. Fazer revisão final de Docker, testes, build, responsividade e apresentação.

## Observações para retomada

- Não começar Clientes ou Usuários antes de estabilizar pessoas, veículos e revisões.
- Não trocar novamente o banco sem testar migrations e relacionamentos no PostgreSQL via Docker.
- Conferir a duplicação da rota inicial em `routes/web.php` antes da entrega.
- O formulário de veículos já está pronto visualmente; o próximo trabalho é o backend do `POST /vehicles`.
