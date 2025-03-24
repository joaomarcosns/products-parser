# Products Parser

![GitHub](https://img.shields.io/github/license/joaomarcosns/products-parser)
![GitHub last commit](https://img.shields.io/github/last-commit/joaomarcosns/products-parser)
![GitHub issues](https://img.shields.io/github/issues/joaomarcosns/products-parser)

## Pré-requisitos

- **Docker**: Para rodar o projeto em containers.
- **Docker Compose**: Para orquestrar os containers.

## Funcionalidades

Este projeto é uma API desenvolvida para realizar a extração de dados nutricionais a partir da URL `https://challenges.coode.sh/food/data/json/`. A API fornece funcionalidades para gerenciar produtos alimentícios na base de dados, incluindo operações de leitura, atualização e remoção.

## Endpoints da API

A documentação das rotas está disponível com OpenAPI 3.0.0 no diretório `docs`. Os arquivos de documentação estão disponíveis nos formatos JSON e YAML:

- [Documentação em JSON](docs/api.json)
- [Documentação em YAML](docs/api.yml)

### Endpoints principais

- `GET /`: Detalhes da API, conexão com a base de dados, horário da última execução do CRON, tempo online e uso de memória.
- `PUT /products/:code`: Atualiza as informações de um produto específico.
- `DELETE /products/:code`: Muda o status de um produto para `trash`.
- `GET /products/:code`: Retorna informações de um produto específico.
- `GET /products`: Lista todos os produtos com paginação.

## Como Rodar

1. Clone o repositório:

   ```bash
   git clone https://github.com/joaomarcosns/products-parser.git

2. Acesse o diretório do projeto:

   ```bash
   cd products-parser
   ```

3. Crie um arquivo `.env` na raiz do projeto com as seguintes variáveis de ambiente:

   ```bash
   cp .env.example .env
   ```

4. Atualize as variáveis de ambiente do arquivo .env

```dosini
APP_NAME="Products Parser"
APP_URL=http://localhost:8989
L5_SWAGGER_CONST_HOST=http://localhost:8989/api/v1
MAIL_FROM_REPORT='email@exemple.com'
APP_VERSION=1.0.0

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=products_parser
DB_USERNAME=root
DB_PASSWORD=password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_API_VERSION=1.0.0
L5_SWAGGER_TITLE="Products Parser API"
L5_SWAGGER_DESCRIPTION="Documentação da API do Products Parser"
L5_SWAGGER_GENERATE_YAML_COPY=true


MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

5. Inicialize o projeto:

   ```bash
   docker-compose up --build -d
   ```

6. Acesse a documentação da API em `http://localhost:3000/docs`.

7. Acessar o container

```sh
docker-compose exec app bash
```

8. Instalar as dependências do projeto

```sh
composer install
```

9. Gerar a key do projeto Laravel

```sh
php artisan key:generate
```

Acessar o projeto
[http://localhost:8989](http://localhost:8989)

## Requisitos

- [Requisitos](REQUIREMENTS.md)
