# Fintech Wallet — Desafio Técnico Pleno

Carteira digital pessoal com depósitos, saques e histórico de transações.

**Stack:** Laravel 10 · Vue 3 · MySQL · Laravel Sanctum · Pinia

---

## Decisões Técnicas

- **Service Layer (`WalletService`)**: toda a lógica financeira (depósito e saque) é isolada em um Service. O Controller apenas delega e responde — sem regra de negócio.
- **Operações atômicas com `DB::transaction` + `lockForUpdate`**: garante que nenhuma inconsistência ocorra em caso de falha ou concorrência.
- **`bcmath` para aritmética monetária**: evita erros de ponto flutuante em operações com valores decimais.
- **Form Requests**: validação de inputs separada do Controller, com mensagens em português.
- **API Resources**: transformação das respostas JSON padronizada e desacoplada dos Models.
- **Pinia (frontend)**: gerenciamento de estado global de autenticação e carteira.
- **Vue Router com guards**: rotas protegidas por autenticação e redirecionamento automático.

---

## Pré-requisitos

- PHP 8.2+
- Composer 2+
- Node.js 18+
- MySQL 8+ (ou MariaDB 10.4+)

---

## Rodando localmente

### Backend (Laravel)

```bash
cd backend

# 1. Instalar dependências
composer install

# 2. Copiar e configurar o .env
cp .env.example .env
php artisan key:generate

# 3. Criar o banco de dados (MySQL)
# Acesse o MySQL e execute:
# CREATE DATABASE fintech_wallet;

# 4. Configurar DB_DATABASE, DB_USERNAME, DB_PASSWORD no .env

# 5. Rodar migrations e seeder
php artisan migrate --seed

# 6. Iniciar servidor
php artisan serve
```

API disponível em: `http://localhost:8000/api`

### Frontend (Vue 3)

```bash
cd frontend

# 1. Instalar dependências
npm install

# 2. Configurar a URL da API (opcional — padrão: http://localhost:8000/api)
cp .env.example .env

# 3. Iniciar servidor de desenvolvimento
npm run dev
```

Aplicação disponível em: `http://localhost:5173`

---

## Testes

```bash
cd backend
php artisan test
```

**10 testes / 36 assertions** cobrindo:
- Registro cria carteira com saldo zero
- Depósito registra transação de crédito
- Saque com saldo suficiente
- Saque com saldo insuficiente retorna 422
- Valores inválidos (zero, negativos) são rejeitados
- Saque mínimo de R$ 0,01
- Histórico paginado com filtro por tipo
- Dashboard com resumo mensal
- Rotas protegidas exigem autenticação

---

## Endpoints da API

| Método | Rota                   | Auth | Descrição                   |
|--------|------------------------|------|-----------------------------|
| POST   | /api/register          | Não  | Cadastro de usuário         |
| POST   | /api/login             | Não  | Login                       |
| POST   | /api/logout            | Sim  | Logout                      |
| GET    | /api/wallet            | Sim  | Saldo da carteira           |
| POST   | /api/wallet/deposit    | Sim  | Realizar depósito           |
| POST   | /api/wallet/withdraw   | Sim  | Realizar saque              |
| GET    | /api/transactions      | Sim  | Histórico paginado          |
| GET    | /api/dashboard         | Sim  | Resumo do dashboard         |

---

## Credenciais do usuário seed

```
E-mail: demo@fintech.com
Senha:  password123
```

---

## Deploy

> Link do deploy: *(a ser preenchido após o deploy)*

---

## Estrutura do Projeto

```
fintech-wallet-pleno/
├── backend/                  # Laravel 10 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/  # Controllers finos
│   │   │   ├── Requests/     # Form Requests (validação)
│   │   │   └── Resources/    # JSON Resources
│   │   ├── Models/           # User, Wallet, Transaction
│   │   ├── Services/         # WalletService (lógica de negócio)
│   │   └── Exceptions/       # InsufficientBalanceException
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── tests/Feature/        # WalletTest (10 testes)
└── frontend/                 # Vue 3 + Pinia + Vue Router
    └── src/
        ├── views/            # Login, Register, Dashboard, Transactions
        ├── stores/           # auth.js, wallet.js
        ├── services/         # api.js (Axios)
        └── router/           # Rotas com guards de autenticação
```
