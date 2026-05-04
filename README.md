# product-service

Symfony 6.4 microservice — the **catalog master**. Manages products and publishes change events to RabbitMQ. Also consumes `OrderPlacedMessage` from the order service to keep its inventory counts in sync.

Part of a four-repo system. See [`tech-task-stack`](https://github.com/shubaivan/tech-task-stack) for the full architecture, live URLs, and end-to-end test recipe.

## Endpoints

| Method | Path | Purpose |
|---|---|---|
| `POST`  | `/products`      | Create a product |
| `GET`   | `/products`      | List all products |
| `GET`   | `/products/{id}` | Show one product |
| `PUT`   | `/products/{id}` | Update an existing product |

Every successful `POST` and `PUT` publishes a `Shared\Message\ProductSyncMessage` to the `products` fanout exchange (consumed by the order service).

## Live URL

https://products.shuba.dev — TLS-enabled, hit it directly with `curl`.

## Request / response examples

### POST `/products` — create

Request:

```json
{
  "name": "Coffee Mug",
  "price": 12.99,
  "quantity": 100
}
```

Response — `201 Created`:

```json
{
  "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
  "name": "Coffee Mug",
  "price": 12.99,
  "quantity": 100
}
```

### GET `/products` — list

Response — `200 OK`:

```json
{
  "data": [
    {
      "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
      "name": "Coffee Mug",
      "price": 12.99,
      "quantity": 100
    }
  ]
}
```

### GET `/products/{id}` — show

Response — `200 OK`:

```json
{
  "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
  "name": "Coffee Mug",
  "price": 12.99,
  "quantity": 100
}
```

Errors: `400` on invalid UUID, `404` if not found.

### PUT `/products/{id}` — update

Request:

```json
{
  "name": "Coffee Mug",
  "price": 14.99,
  "quantity": 80
}
```

Response — `200 OK`:

```json
{
  "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
  "name": "Coffee Mug",
  "price": 14.99,
  "quantity": 80
}
```

Errors: `400` on invalid UUID, `404` if not found. Re-publishes a `ProductSyncMessage` so the order-service mirror picks up the new values.

## Try it

```bash
curl -s -X POST https://products.shuba.dev/products \
  -H 'Content-Type: application/json' \
  -d '{"name":"Coffee Mug","price":12.99,"quantity":100}'
```

## Run locally

This service expects RabbitMQ + PostgreSQL from [`tech-task-stack`](https://github.com/shubaivan/tech-task-stack) on the shared `application` docker network.

```bash
cd docker && docker compose up -d
# service then available at http://products.loc
```

Prereqs: `127.0.0.1 products.loc` in `/etc/hosts`, the `application` docker network created, the stack from `tech-task-stack` already up.

## Key files

- `src/Controller/ProductController.php` — HTTP endpoints
- `src/Entity/Product.php` — extends `Shared\Entity\ProductBase` (mapped superclass)
- `src/MessageHandler/OrderPlacedHandler.php` — consumes order events to decrement master quantity
- `config/packages/messenger.yaml` — AMQP transport config

## Tech

PHP 8.3 · Symfony 6.4 · Doctrine ORM 3 · PostgreSQL · RabbitMQ via Symfony Messenger
