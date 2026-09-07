# 🎓 PractiHub API REST
 
API REST desarrollada en Laravel para la gestión de prácticas de estudiantes en empresas. Permite a las empresas publicar ofertas de prácticas y a los estudiantes inscribirse a ellas, con autenticación mediante tokens (Laravel Passport) y control de acceso basado en roles.
 
Proyecto desarrollado como conversión de una aplicación MVC (Sprint 4) a una arquitectura de API REST.
 
## 🛠️ Tecnologías
 
- **PHP** ^8.3
- **Laravel** ^13.8
- **Laravel Passport** ^13.0 — autenticación mediante tokens OAuth2
- **Pest** ^5.0 — testing
- **SQLite** — base de datos (configurable)
- **Scribe** — generación automática de documentación de la API
## ✨ Funcionalidades
 
### Estudiante
- Registro, inicio y cierre de sesión
- Ver, editar y eliminar su propio perfil
- Consultar ofertas de prácticas (con filtro por categoría y ubicación)
- Inscribirse y desapuntarse de ofertas
- Consultar el estado de sus candidaturas
### Empresa
- Registro, inicio y cierre de sesión
- Ver, editar y eliminar su propio perfil
- Publicar, editar y eliminar sus ofertas de prácticas
- Ver los candidatos inscritos a sus ofertas y consultar su CV
- Aceptar o rechazar candidaturas
- Consultar el ranking de empresas por porcentaje de aceptación
## Recursos principales
 
| Recurso | Descripción |
|---|---|
| `users` | Estudiantes y empresas (diferenciados por el campo `role`) |
| `offers` | Ofertas de prácticas publicadas por empresas |
| `applications` | Candidaturas de estudiantes a ofertas |
| `categories` | Categorías para clasificar y filtrar ofertas |
 
## Reglas de negocio destacadas
 
- **Flujo de una candidatura:** `pending` → `read` (al abrir la empresa el CV) → `accepted` / `rejected`. Solo se puede aceptar o rechazar una candidatura que ya esté en estado `read`.
- **Retirada de candidatura:** el estudiante solo puede retirar una candidatura mientras esté en estado `pending` y dentro de los primeros 30 minutos desde que se creó.
- **Ranking de empresas:** se calcula el % de aceptación (`accepted / (accepted + rejected) * 100`) contando únicamente candidaturas ya resueltas, sobre todas las ofertas de la empresa (activas e inactivas). Las empresas sin ninguna candidatura resuelta no aparecen en el ranking.
- **Visibilidad de ofertas:** los estudiantes solo ven ofertas activas; las empresas ven todas las suyas, activas e inactivas.
## Instalación
 
### Requisitos previos
- PHP 8.3+
- Composer
- [Laravel Herd](https://herd.laravel.com/) (o cualquier entorno equivalente)
### Pasos
 
1. Clonar el repositorio e instalar dependencias:
```bash
   git clone <url-del-repositorio>
   cd PractiHub-API-REST
   composer install
```
 
2. Copiar el archivo de entorno y generar la clave de la aplicación:
```bash
   cp .env.example .env
   php artisan key:generate
```
 
3. Configurar la base de datos en `.env` (por defecto SQLite):
```bash
   touch database/database.sqlite
```
 
4. Ejecutar las migraciones y poblar la base de datos con datos de ejemplo:
```bash
   php artisan migrate --seed
```
 
5. Instalar Passport y crear un cliente de acceso personal (necesario para poder generar tokens):
```bash
   php artisan passport:client --personal
```
 
6. Levantar el servidor (si no usas Herd, con el servidor embebido de Laravel):
```bash
   php artisan serve
```
 
## Usuarios de prueba (seeder)
 
Tras ejecutar `php artisan migrate --seed`, se crean automáticamente los siguientes usuarios, además de empresas, estudiantes, ofertas y candidaturas aleatorias:
 
| Rol | Email | Contraseña |
|---|---|---|
| Empresa | `empresa@test.com` | `password123` |
| Estudiante | `estudiante@test.com` | `password123` |
 
## Testing
 
El proyecto cuenta con tests funcionales (Pest) que cubren autenticación, autorización por rol, validaciones y las reglas de negocio principales (candidaturas, ranking, etc.):
 
```bash
php artisan test
```
 
## Documentación de la API
 
La documentación completa de todos los endpoints (parámetros, ejemplos de petición/respuesta y un explorador interactivo "Try it out") se genera con [Scribe](https://scribe.knuckles.wtf/laravel/):
 
```bash
php artisan scribe:generate
```
 
Una vez generada, está disponible en:
 
- **Documentación interactiva:** `/docs`
- **Colección de Postman:** `storage/app/private/scribe/collection.json`
- **Especificación OpenAPI:** `storage/app/private/scribe/openapi.yaml`
## Endpoints principales
 
| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/api/register` | Registro de estudiante o empresa |
| `POST` | `/api/login` | Inicio de sesión |
| `POST` | `/api/logout` | Cierre de sesión |
| `GET/PUT/DELETE` | `/api/users/{user}` | Perfil de estudiante |
| `GET/PUT/DELETE` | `/api/companies/{company}` | Perfil de empresa |
| `GET` | `/api/companies/ranking` | Ranking de empresas por % de aceptación |
| `GET` | `/api/categories` | Listado de categorías |
| `GET/POST/PUT/DELETE` | `/api/offers` / `/api/offers/{offer}` | Ofertas de prácticas |
| `POST` | `/api/offers/{offer}/applications` | Inscribirse a una oferta |
| `GET` | `/api/offers/{offer}/applications` | Ver candidatos de una oferta (empresa) |
| `GET/PUT/DELETE` | `/api/applications/{application}` | Detalle / gestión de una candidatura |
| `GET` | `/api/applications` | Listado de candidaturas propias |
| `GET` | `/api/applications/{application}/cv` | Ver CV de una candidatura |
 
> Para el listado completo con parámetros y ejemplos, consulta la documentación generada en `/docs`.
 
## Autenticación
 
Todos los endpoints (salvo `register` y `login`) requieren autenticación mediante un token Bearer de Passport:
 
```
Authorization: Bearer {tu_token}
```
 
El token se obtiene en la respuesta de `/api/register` o `/api/login`.
