# product-service

Symfony 6.4 microservice — owns the product catalog. Publishes a `ProductSyncMessage` to RabbitMQ on every create/update so other services can keep a local mirror.

Endpoints:

- `POST /products` — create
- `GET /products` — list
- `GET /products/{id}` — fetch one
- `PUT /products/{id}` — update
- `GET /api/doc` — Swagger UI

## Run locally

```bash
docker compose up -d
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
symfony serve -d
```

Try it:

```bash
curl -X POST http://127.0.0.1:8000/products \
  -H 'Content-Type: application/json' \
  -d '{"name":"Coffee Mug","price":12.99,"quantity":100}'
```

RabbitMQ management UI: http://127.0.0.1:15672 (guest / guest).

## Sample response

```json
{
  "id": "0190e1a8-...",
  "name": "Coffee Mug",
  "price": 12.99,
  "quantity": 100
}
```
