# Metricalo Coding Challenge

### Table of Contents

- [Overview](#overview)
- [Application](#application)
    - [Requirements](#requirements)
    - [Environment Setup](#environment-setup)
    - [Running the Application](#running-the-application)
- [HTTP API endpoints](#http-api-endpoints)
- [CLI Command](#cli-command)

---

### Overview

This repository contains a coding challenge for **Metricalo**, focusing on implementing a payment processing system with
two payment gateways: **Shift4** and **ACI**.

The application is organized using Domain-Driven Design (DDD) principles. This approach structures the codebase into
distinct layers such as Domain, Application, Infrastructure and Presentation, promoting separation of concerns,
maintainability, and scalability.
The DDD architecture enables clear boundaries between business logic and technical implementation, making the system
robust and extensible for future payment gateways.

The application is designed to be run in a Docker environment, making it easy to set up and run on any machine with
Docker installed. The code follows best practices for PHP development, including unit and integration tests,
coding standards checks, and static code analysis.

The application provides a **RESTful API** and **CLI command** for processing payments through the two gateways,
**Shift4** and **ACI**.

---

### Application

The application is built using PHP 8.2, Symfony 6.4, and PostgreSQL 17.4, leveraging Docker for containerization.

#### Requirements

To run this application, you'll need:

- Docker Engine
- Docker Compose
- Make (GNU Make or compatible)
- Git

The application runs the following services in containers:
- PHP 8.2
- PostgreSQL 17.4
- Nginx 1.25

No additional local dependencies are required as everything runs inside Docker containers.

Note: Ensure ports 80 (for Nginx) and 5432 (for PostgreSQL) are available on your host machine, or update the port mappings in your `.env` file.

#### Environment Setup

1. Copy the example environment file:

```bash
cp .env.dist .env
```
2. Update `.env` with your configuration (DB credentials, ports, etc.).

#### Running the Application

Build containers:

```bash
docker-compose build
```
Run containers:

```bash
docker-compose up -d
```

Stop containers:

```bash
docker-compose down
```

Enter into php container

```bash
make bash
```

Install dependencies

```bash
make install
```

Run migrations

```bash
make run-migrations
```

Run all Tests

```bash
make tests
```

Run Unit Tests

```bash
make unit
``` 

Run Integration Tests

```bash
make integration
``` 

Run Coding Standards checks

```bash
make cs
``` 

Run Static Code Analysis

```bash
make stan
``` 
---

### HTTP API endpoints
The application exposes a RESTful API endpoint `POST /app/example/{gateway}` for payment processing:

| Endpoint              | Method | Description                            |
|-----------------------|--------|----------------------------------------|
| `/app/example/shift4` | POST   | Process payment through Shift4 gateway |
| `/app/example/aci`    | POST   | Process payment through ACI gateway    |

#### Request Parameters
All endpoints accept the following JSON parameters:

| Parameter      | Type    | Description                    | Required |
|----------------|---------|--------------------------------|----------|
| `amount`       | integer | Payment amount in cents        | Yes      |
| `currency`     | string  | Currency code (USD, EUR, etc.) | Yes      |
| `cardHolder`   | string  | Name on the card               | Yes      |
| `cardNumber`   | string  | Card number without spaces     | Yes      |
| `cardExpMonth` | integer | Card expiration month (1-12)   | Yes      |
| `cardExpYear`  | integer | Card expiration year (YYYY)    | Yes      |
| `cardCvv`      | string  | Card security code             | Yes      |

#### Example HTTP Requests
You can interact with the API using tools like `curl` or Postman. Here are some example requests:

Shift4 Payment Example:
```bash
curl -X POST http://localhost/app/example/shift4 \
       -H "Content-Type: application/json" \
       -d '{
         "amount": 2500,     
         "currency": "USD",  
         "cardHolder": "Jane Jones",
         "cardNumber": "4242424242424242",
         "cardExpMonth": 12,
         "cardExpYear": 2029,
         "cardCvv": "123"   
       }'
```

ACI Payment Example:
```bash
curl -X POST http://localhost/app/example/aci \
       -H "Content-Type: application/json" \
       -d '{
         "amount": 9200,                     
         "currency": "EUR",                  
         "cardHolder": "Joe Pass",          
         "cardNumber": "4200000000000000",  
         "cardExpMonth": 6,                
         "cardExpYear": 2034,              
         "cardCvv": "456"                   
       }'
```

### CLI Command
The application provides a CLI command for payment processing: `bin/console app:example`

#### Command Options
Command accept these options:

| Argument       | Type    | Description                     | Required |
|----------------|---------|---------------------------------|----------|
| `gateway`      | string  | Payment Gateway (shift4 or aci) | Yes      |
| `amount`       | integer | Payment amount in cents         | Yes      |
| `currency`     | string  | Currency code (USD, EUR, etc.)  | Yes      |
| `cardHolder`   | string  | Name on the card                | Yes      |
| `cardNumber`   | string  | Card number without spaces      | Yes      |
| `cardExpMonth` | integer | Card expiration month (1-12)    | Yes      |
| `cardExpYear`  | integer | Card expiration year (YYYY)     | Yes      |
| `cardCvv`      | string  | Card security code              | Yes      |

#### Example CLI Commands

You can use the CLI to interact with the API. Here are some example commands:

Shift4 Payment Example:
```bash
bin/console app:example "shift4" 2500 "USD" "Jane Jones" "4242424242424242" 2029 12 "123"
```

ACI Payment Example:
```bash
bin/console app:example "aci" 9200 "EUR" "Joe Pass" "4200000000000000" 2034 06 "456"
```