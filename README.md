# SNS (Social Networking Service)

## Getting started

### Requirements (Recommended)

- PHP >= 8.2.12
- Composer
- MySQL / MariaDB

### Installation

1. Clone repository
2. Jalankan `composer install` atau `composer i` pada terminal
3. Buat file baru bernama `.env` di root (sejajar dengan file `.env.example`)
4. Salin isi file `.env.example` ke file `.env`
5. Lakukan konfigurasi pada file `.env`
6. Jalankan query yang terdapat pada [`Database.sql`](./src/Database/Database.sql)

## Documentation

### RESTful API Specification

Silakan buka [`openapi.yaml`](./docs/openapi.yaml) dengan menggunakan [`Swagger`](https://swagger.io/), [
`OpenAPI`](https://www.openapis.org/), atau tool lain yang mendukung OpenAPI 3.0.0.

## FAQ

### Error

Q:  Mengalami error `Provided key is too short`  
A:  Periksa value `JWT_SECRET` pada file `.env`.  
`JWT_SECRET` disarankan memiliki panjang minimal **32 karakter ASCII**, bersifat acak, dan tidak mengandung karakter
non-ASCII.