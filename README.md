# Habitanto Fake Store Wrapper

Aplicacion Laravel + Livewire que integra `products` y `carts` desde Fake Store API mediante una capa interna unificada. La interfaz no consume multiples Web Services externos ni conoce sus detalles tecnicos: todo pasa por un wrapper interno que valida, transforma y normaliza las respuestas antes de llegar a la UI o a la API interna del proyecto.

## TL;DR

- El proyecto real vive en `habitanto/`.
- La UI publica esta en `GET /fake-store`.
- La API interna unificada esta bajo `GET|POST|PUT|PATCH|DELETE /api/fake-store/*`.
- Livewire consume una sola fachada interna: `ExternalServicesFacade`.
- Los Web Services externos solo se consumen desde `FakeStoreApiClient`.
- La suite actual protege contrato, errores, flujo Livewire y limite arquitectonico entre UI y capa de integracion.

## En Menos de 5 Minutos

### Que problema resuelve

El cliente pide consumir varios Web Services externos, pero no quiere exponerlos como varias APIs distintas hacia la interfaz. Este proyecto resuelve eso con una fachada interna que presenta `products` y `carts` como una sola fuente consistente.

### Como esta organizado

```text
Livewire / Controllers
        |
        v
ExternalServicesFacade
        |
        v
ProductService / CartService
        |
        v
ProductRepository / CartRepository
        |
        v
FakeStoreApiClient
        |
        v
Fake Store API
```

### Que debes ejecutar

```bash
cd habitanto
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Abrir:

- `http://127.0.0.1:8000/fake-store`

Si usas Homely:

- mapear `habitanto.voc` hacia `habitanto/public`
- ajustar `APP_URL`
- reiniciar `php8.4-fpm` y `nginx` si cambias configuracion

## Tabla de Contenidos

- [Contexto y Objetivo](#contexto-y-objetivo)
- [Principios de Arquitectura](#principios-de-arquitectura)
- [Stack Tecnologico](#stack-tecnologico)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Arquitectura General](#arquitectura-general)
- [Flujo Completo de la Aplicacion](#flujo-completo-de-la-aplicacion)
- [Wrapper de Servicios Externos](#wrapper-de-servicios-externos)
- [Contratos Publicos](#contratos-publicos)
- [Manejo de Errores](#manejo-de-errores)
- [Observabilidad y Logging](#observabilidad-y-logging)
- [Patrones de Diseno](#patrones-de-diseno)
- [SOLID, DRY y Separation of Concerns](#solid-dry-y-separation-of-concerns)
- [Testing Strategy](#testing-strategy)
- [ADR y Restricciones](#adr-y-restricciones)
- [Como Extender el Sistema](#como-extender-el-sistema)
- [Instalacion y Ejecucion](#instalacion-y-ejecucion)
- [Troubleshooting](#troubleshooting)

## Contexto y Objetivo

### Objetivo funcional

Consumir recursos externos de Fake Store API y exponerlos hacia la UI y la API interna como un unico punto de integracion, con contratos controlados y manejo de errores consistente.

### Objetivo arquitectonico

- evitar que Livewire consuma `Http::` o URLs externas
- evitar acoplar la UI a la forma cruda del proveedor externo
- encapsular conectividad, validacion y errores en capas internas
- mantener contratos publicos estables aunque cambie el proveedor
- permitir agregar nuevos proveedores sin reescribir la pantalla

## Principios de Arquitectura

- El frontend nunca habla directo con proveedores externos.
- La UI y los endpoints internos dependen de una sola fachada interna.
- Toda respuesta externa se valida antes de entrar al sistema.
- Todo error externo se traduce a un mensaje controlado.
- Las decisiones de integracion viven fuera de Livewire.
- Los contratos publicos deben mantenerse aunque la integracion interna evolucione.

## Stack Tecnologico

- PHP 8.4 en desarrollo local
- Laravel Framework `^13.8`
- Livewire `^4.3`
- Tailwind CSS 4
- PHPUnit `^12.5`
- Laravel HTTP Client
- Fake Store API como proveedor externo de referencia

## Estructura del Proyecto

El repositorio contiene la aplicacion Laravel dentro de `habitanto/`.

```text
habitanto/
|-- app/
|   |-- Data/FakeStore/
|   |   |-- Requests/
|   |   `-- Responses/
|   |-- Exceptions/
|   |-- Http/
|   |   |-- Controllers/
|   |   `-- Requests/FakeStore/
|   |-- Livewire/
|   |-- Repositories/FakeStore/
|   |-- Services/FakeStore/
|   `-- Support/FakeStore/
|-- config/
|-- resources/views/
|-- routes/
`-- tests/
```

### Responsabilidad por carpeta

- `app/Livewire`
  - estado de pantalla, acciones de usuario y rendering reactivo
- `app/Http/Controllers`
  - entrada web y API interna
- `app/Http/Requests/FakeStore`
  - validacion de entrada HTTP
- `app/Services/FakeStore`
  - coordinacion de casos de uso y fachada unificada
- `app/Repositories/FakeStore`
  - acceso a proveedores externos
- `app/Data/FakeStore`
  - DTOs de entrada y salida
- `app/Support/FakeStore`
  - respuestas JSON internas y validacion de payloads externos
- `app/Exceptions`
  - normalizacion de excepciones de integracion
- `tests`
  - pruebas de contrato, flujo y limites arquitectonicos

## Arquitectura General

### Diagrama ASCII

```text
                         +---------------------------+
                         |   Browser / API Client    |
                         +-------------+-------------+
                                       |
                    +------------------+------------------+
                    |                                     |
                    v                                     v
     +-------------------------------+      +-------------------------------+
     | /fake-store                   |      | /api/fake-store/*             |
     | Blade + Livewire Dashboard    |      | ProductController / Cart...   |
     +---------------+---------------+      +---------------+---------------+
                     |                                      |
                     +------------------+-------------------+
                                        |
                                        v
                    +---------------------------------------+
                    | ExternalServicesFacade                |
                    | Punto unico de integracion interno    |
                    +------------------+--------------------+
                                       |
                  +--------------------+--------------------+
                  |                                         |
                  v                                         v
     +-------------------------------+      +-------------------------------+
     | ProductService                |      | CartService                   |
     +---------------+---------------+      +---------------+---------------+
                     |                                      |
                     v                                      v
     +-------------------------------+      +-------------------------------+
     | ProductRepository             |      | CartRepository                |
     +---------------+---------------+      +---------------+---------------+
                     |                                      |
                     +------------------+-------------------+
                                        |
                                        v
                    +---------------------------------------+
                    | FakeStoreApiClient                    |
                    | Cliente HTTP unico hacia proveedor    |
                    +------------------+--------------------+
                                       |
                                       v
                    +---------------------------------------+
                    | Fake Store API                        |
                    +---------------------------------------+
```

### Porque esta arquitectura

- La UI queda aislada de cambios de proveedor.
- La validacion de payloads externos no contamina la vista.
- Los errores de red, timeout y formato se resuelven en un solo lugar.
- La API interna y Livewire comparten el mismo contrato de integracion.
- El sistema puede crecer por proveedor o por recurso sin duplicar logica en la vista.

## Flujo Completo de la Aplicacion

### Flujo de carga del dashboard

```text
GET /fake-store
-> routes/web.php
-> FakeStoreDashboardController
-> resources/views/fake-store/dashboard.blade.php
-> <livewire:fake-store-dashboard />
-> FakeStoreDashboard::mount()
-> FakeStoreDashboard::refreshDashboard()
-> ExternalServicesFacade::getDashboardSnapshot()
-> ProductService::getAll() / CartService::getAll()
-> ProductRepository::all() / CartRepository::all()
-> FakeStoreApiClient::get('/products') / get('/carts')
-> Fake Store API
-> FakeStoreResponseValidator
-> ProductResponseData[] / CartResponseData[]
-> Livewire convierte DTOs a arrays
-> render()
-> resources/views/livewire/fake-store-dashboard.blade.php
```

### Flujo de guardado de producto

```text
Usuario hace click en "Guardar producto"
-> FakeStoreDashboard::saveProduct()
-> validacion Livewire
-> ProductRequestData::fromArray()
-> ExternalServicesFacade::createProduct() o updateProduct()
-> ProductService
-> ProductRepository
-> FakeStoreApiClient::post('/products') o put('/products/{id}')
-> Fake Store API
-> FakeStoreResponseValidator::assertProduct()
-> ProductResponseData
-> Livewire actualiza selectedProduct y collection live
-> render()
-> UI refrescada
```

### Flujo de API interna

```text
GET /api/fake-store/products
-> ProductController::index()
-> ExternalServicesFacade::getAllProducts()
-> ProductService
-> ProductRepository
-> FakeStoreApiClient
-> Fake Store API
-> DTOs Response
-> ApiResponse::success()
-> JSON normalizado
```

## Wrapper de Servicios Externos

### Que es

`ExternalServicesFacade` es la capa unificada que oculta la existencia de multiples recursos o proveedores externos. No es una facade estatica de Laravel; es una fachada de aplicacion que orquesta servicios internos y estandariza comportamiento.

Archivo clave:

- `habitanto/app/Services/FakeStore/ExternalServicesFacade.php`

### Que resuelve

- entrega un unico punto de entrada para `products` y `carts`
- centraliza logging y reporte de errores
- evita que Livewire conozca `ProductService`, `CartService` o `Http::`
- conserva contratos internos estables
- soporta snapshots para el dashboard sin propagar detalles de integracion

### Porque no se consume el WS directo desde Livewire

Si Livewire conociera el WS:

- la UI tendria logica de red
- se duplicaria manejo de errores
- quedaria acoplada a rutas y payloads externos
- cambiar de proveedor romperia pantalla y tests

La fachada existe precisamente para evitar ese acoplamiento.

## Contratos Publicos

### Superficies publicas del sistema

- Vista web: `GET /fake-store`
- API interna:
  - `GET /api/fake-store/products`
  - `POST /api/fake-store/products`
  - `GET /api/fake-store/products/{product}`
  - `PUT /api/fake-store/products/{product}`
  - `PATCH /api/fake-store/products/{product}`
  - `DELETE /api/fake-store/products/{product}`
  - `GET /api/fake-store/carts`
  - `POST /api/fake-store/carts`
  - `GET /api/fake-store/carts/{cart}`
  - `PUT /api/fake-store/carts/{cart}`
  - `PATCH /api/fake-store/carts/{cart}`
  - `DELETE /api/fake-store/carts/{cart}`

### Contrato JSON de salida

Todas las respuestas HTTP internas usan `ApiResponse`.

Exito:

```json
{
  "success": true,
  "message": "Productos obtenidos correctamente.",
  "data": [],
  "meta": []
}
```

Error:

```json
{
  "success": false,
  "message": "Fake Store API devolvio un error al procesar /products.",
  "errors": {
    "method": "GET"
  }
}
```

### Contrato de entrada para products

```json
{
  "id": 10,
  "title": "Keyboard",
  "price": 59.99,
  "description": "Mechanical keyboard",
  "category": "tech",
  "image": "https://example.com/keyboard.png"
}
```

`id` es opcional en create y se usa cuando el flujo es update.

### Contrato de entrada para carts

```json
{
  "id": 7,
  "userId": 15,
  "products": [
    {
      "id": 4,
      "title": "Desk Lamp",
      "price": 40.50,
      "description": "Warm light",
      "category": "home",
      "image": "https://example.com/lamp.png",
      "quantity": 2
    }
  ]
}
```

### Contrato de salida tipado interno

Antes de llegar a la UI o a los controllers, la integracion se transforma a DTOs:

- `ProductResponseData`
- `CartResponseData`
- `DeleteResponseData`

Esto evita propagar arrays crudos del proveedor por todo el sistema.

## Manejo de Errores

### Tipos de error normalizados

- conexion fallida
- timeout
- `400`
- `401`
- `403`
- `404`
- `422`
- `500+`
- respuesta invalida
- payload incompleto
- error interno inesperado

### Flujo de errores

```text
Proveedor externo falla
-> FakeStoreApiClient detecta ConnectionException o response->failed()
-> FakeStoreException traduce el fallo a un mensaje de negocio tecnico-controlado
-> ExternalServicesFacade registra Log::error()
-> Controller o Livewire recibe una excepcion normalizada
-> ApiResponse o la UI muestran mensaje seguro para el usuario
```

### Diagrama ASCII de error

```text
Fake Store API / Red
        |
        v
FakeStoreApiClient
        |
        v
FakeStoreException
  - connection()
  - timeout()
  - upstream()
  - invalidResponse()
  - unexpected()
        |
        v
ExternalServicesFacade::report()
        |
        +--> Log::error()
        |
        +--> Controller => ApiResponse::error()
        |
        +--> Livewire => errorMessage
```

### Principios de manejo

- no se muestran errores tecnicos crudos en pantalla
- los detalles utiles para diagnostico van al log
- los mensajes son consistentes entre UI y API
- los contratos de error son previsibles para el consumidor

## Observabilidad y Logging

### Que se registra

La fachada registra eventos de error con:

- operacion interna
- mensaje normalizado
- status
- contexto del fallo

### Donde observar

- `habitanto/storage/logs/laravel.log`
- `php artisan pail`

### Comandos utiles

```bash
cd habitanto
php artisan pail
tail -n 80 storage/logs/laravel.log
```

### Que buscar en logs

- `External services facade error`
- `Fake Store products dashboard error`
- `Fake Store carts dashboard error`
- `cURL error 28`
- `missing_keys`

## Patrones de Diseno

### Service

`ProductService` y `CartService` encapsulan operaciones de aplicacion por recurso.

### Repository

`ProductRepository` y `CartRepository` concentran el acceso al proveedor externo y la traduccion del payload.

### Facade / Wrapper

`ExternalServicesFacade` ofrece una unica cara interna hacia la UI y la API.

### DTO

Los objetos en `app/Data/FakeStore` desacoplan la capa interna de arrays arbitrarios y vuelven explicitos los contratos.

### Adapter

Los repositorios actuan como adaptadores entre el formato del proveedor externo y el formato interno tipado.

### Boundary Test

La suite valida que Livewire no se acople a `Http::`, `FakeStoreApiClient` ni servicios individuales.

## SOLID, DRY y Separation of Concerns

### Single Responsibility Principle

- Livewire maneja estado y eventos de UI.
- Controllers exponen endpoints.
- Services coordinan casos de uso.
- Repositories consumen el proveedor.
- Validators revisan la forma del payload.
- Exceptions traducen errores tecnicos.

### Open/Closed Principle

La UI depende de una fachada estable. Es posible cambiar el proveedor o agregar otro recurso extendiendo servicios y repositorios, sin reescribir la vista principal.

### Liskov Substitution Principle

No hay una jerarquia polimorfica formal en este punto, pero la documentacion y la estructura favorecen futuras abstracciones por interfaz sin romper consumidores.

### Interface Segregation Principle

El sistema aun no define interfaces dedicadas para facade, repository o client. Es una mejora posible si el numero de proveedores crece.

### Dependency Inversion Principle

La inversion es parcial. Laravel resuelve dependencias por inyeccion, pero todavia se depende de clases concretas en varias capas. La arquitectura ya esta preparada para refactor incremental a contratos.

### DRY

- `ApiResponse` evita duplicar forma JSON
- `FakeStoreException` evita repetir traduccion de errores
- `FakeStoreApiClient` evita repetir configuracion HTTP
- `FakeStoreResponseValidator` evita validaciones dispersas

### Separation of Concerns

La UI, la validacion de entrada, la integracion externa, la normalizacion de errores y la construccion de respuesta estan separadas en archivos distintos y con limites claros.

## Testing Strategy

La estrategia de testing esta orientada a proteger comportamiento, contrato y arquitectura.

### Feature Tests

Validan flujos reales de HTTP o Livewire dentro de Laravel.

Casos actuales:

- render del dashboard
- lectura de colecciones
- creacion de carrito
- actualizacion de la coleccion Livewire tras crear producto

### Contract Tests

Validan que el wrapper mantenga el contrato esperado al transformar respuestas.

Casos actuales:

- `ExternalServicesFacade` devuelve `ProductResponseData`
- la API interna mantiene estructura `success`, `message`, `data`, `meta`

### Boundary Tests

Validan limites arquitectonicos.

Caso actual:

- Livewire no consume `Http::`, `FakeStoreApiClient`, `ProductService` ni `CartService` directamente

### Error Handling Tests

Protegen normalizacion de errores y respuestas invalidas.

Casos actuales:

- `403` upstream
- timeout
- respuesta no-array
- datos incompletos

### Unit Tests

La base del proyecto incluye capa para pruebas unitarias, pero hoy la cobertura significativa esta concentrada en Feature, Contract y Boundary. Si el sistema crece, los mejores candidatos para Unit Tests son:

- `FakeStoreResponseValidator`
- `FakeStoreException`
- DTOs request/response
- normalizacion local de colecciones en Livewire

### Comandos

```bash
cd habitanto
php artisan test
composer test
vendor/bin/pint --test
```

## ADR y Restricciones

### ADR-001: La UI no consume proveedores externos directamente

Decision:

- Livewire y los controllers solo consumen `ExternalServicesFacade`.

Razon:

- reduce acoplamiento
- centraliza errores
- simplifica evolucion del backend

### ADR-002: Toda respuesta externa se valida antes de propagarse

Decision:

- repositorios validan colecciones y entidades con `FakeStoreResponseValidator`.

Razon:

- evita que payloads incompletos contaminen la UI o la API interna

### ADR-003: Los errores se normalizan a una excepcion de dominio de integracion

Decision:

- `FakeStoreException` traduce red, timeout, upstream y formato.

Razon:

- unifica comportamiento
- evita mensajes incoherentes por capa

### ADR-004: La API interna conserva contratos estables

Decision:

- no exponer estructuras crudas del proveedor.

Razon:

- permite cambiar integracion sin romper consumidores

### Restricciones actuales del proyecto

- no romper rutas actuales
- no cambiar nombres de metodos publicos ya usados
- no exponer multiples proveedores directamente al frontend
- no mover logica de integracion a Livewire
- no romper la estructura JSON de `ApiResponse`

## Como Extender el Sistema

### Objetivo

Agregar un nuevo recurso o incluso un nuevo proveedor sin romper la UI actual.

### Ruta segura para agregar un nuevo proveedor

1. Crear un cliente o adapter del nuevo proveedor.
2. Crear un repository por recurso que traduzca el payload externo a DTOs internos.
3. Crear o extender un service de aplicacion.
4. Exponer el caso de uso via `ExternalServicesFacade` o una fachada equivalente.
5. Mantener el contrato interno hacia Livewire y controllers.
6. Agregar pruebas de contrato, error y boundary.

### Regla clave

La UI no debe saber si los datos vienen de Fake Store API, de otro proveedor o de varios proveedores compuestos.

### Ejemplo conceptual

```text
NuevoProveedorApiClient
-> NuevoProveedorProductRepository
-> ProductService
-> ExternalServicesFacade
-> FakeStoreDashboard
```

Si el contrato interno se conserva, la vista no necesita cambios estructurales.

## Instalacion y Ejecucion

### Variables relevantes

Configurar en `habitanto/.env`:

```env
APP_URL=http://habitanto.voc

FAKESTORE_BASE_URL=https://fakestoreapi.com
FAKESTORE_TIMEOUT=10
FAKESTORE_CONNECT_TIMEOUT=5
FAKESTORE_FORCE_IP_RESOLVE=v4
```

### Instalacion

```bash
cd habitanto
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Ejecucion local

```bash
cd habitanto
php artisan serve
```

### Ejecucion con tooling del proyecto

```bash
cd habitanto
composer dev
```

### Homely

Ejemplo de mapeo:

```yaml
- map: habitanto.voc
  to: /home/ccalvopina/custom_devs/habitanto_laravel/habitanto/public
  type: laravel
  php: "8.4"
```

## Troubleshooting

### El dashboard muestra "No fue posible conectar con Fake Store API"

1. Validar conectividad desde el runtime de PHP de Laravel:

```bash
cd habitanto
php8.4 -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo Illuminate\Support\Facades\Http::get('https://fakestoreapi.com/products')->status(), PHP_EOL;"
```

2. Si la terminal responde pero la app web no, revisar diferencias entre CLI y FPM.

3. Reiniciar servicios si usas Homely:

```bash
sudo service php8.4-fpm restart
sudo service nginx restart
```

4. Limpiar caches:

```bash
cd habitanto
php artisan optimize:clear
```

5. Revisar logs:

```bash
cd habitanto
tail -n 80 storage/logs/laravel.log
```

### Aparece `cURL error 28: Resolving timed out`

Probable causa:

- timeout o problema de resolucion DNS desde PHP-FPM

Acciones:

- probar `FAKESTORE_FORCE_IP_RESOLVE=v4`
- revisar salida de PHP CLI vs PHP-FPM
- confirmar que el host `fakestoreapi.com` resuelve desde el entorno web

### La coleccion live no refleja create o update

Revisar:

- que la accion se ejecute en `FakeStoreDashboard::saveProduct()` o `saveCart()`
- que la respuesta del proveedor tenga forma valida
- que no exista un error normalizado bloqueando el flujo
- que la sesion de Laravel este funcionando correctamente para overlays locales

### La ruta `https://habitanto.voc/fake-store` no abre

Verificar:

- mapping correcto de Homely
- `APP_URL`
- `nginx` y `php8.4-fpm`
- que el document root apunte a `habitanto/public`

## Archivos Clave

- `habitanto/app/Livewire/FakeStoreDashboard.php`
- `habitanto/app/Services/FakeStore/ExternalServicesFacade.php`
- `habitanto/app/Services/FakeStore/ProductService.php`
- `habitanto/app/Services/FakeStore/CartService.php`
- `habitanto/app/Repositories/FakeStore/FakeStoreApiClient.php`
- `habitanto/app/Repositories/FakeStore/ProductRepository.php`
- `habitanto/app/Repositories/FakeStore/CartRepository.php`
- `habitanto/app/Support/FakeStore/FakeStoreResponseValidator.php`
- `habitanto/app/Support/FakeStore/ApiResponse.php`
- `habitanto/app/Exceptions/FakeStoreException.php`
- `habitanto/tests/Feature/Services/FakeStore/ExternalServicesFacadeTest.php`
- `habitanto/tests/Feature/Architecture/LivewireWrapperBoundaryTest.php`

## Estado Actual

El proyecto ya cumple estas decisiones base:

- existe una fachada unificada para `products` y `carts`
- Livewire no consume APIs externas directamente
- las respuestas externas se validan
- los errores se normalizan
- la UI y la API interna comparten la misma capa de integracion

La siguiente evolucion natural, si el proyecto creciera, seria introducir interfaces explicitas por servicio y repositorio para soportar multiples proveedores con menor acoplamiento a clases concretas.
