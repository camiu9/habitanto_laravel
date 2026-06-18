<?php

namespace App\Http\Controllers\Api\FakeStore;

use App\Data\FakeStore\Requests\ProductRequestData;
use App\Exceptions\FakeStoreException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FakeStore\StoreProductRequest;
use App\Http\Requests\FakeStore\UpdateProductRequest;
use App\Services\FakeStore\ExternalServicesFacade;
use App\Support\FakeStore\ApiResponse;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        private readonly ExternalServicesFacade $service,
    ) {}

    public function index()
    {
        try {
            $products = array_map(
                fn ($product): array => $product->toArray(),
                $this->service->getAllProducts(),
            );

            return ApiResponse::success($products, 'Productos obtenidos correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al consultar productos.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->service->createProduct(ProductRequestData::fromArray($request->validated()));

            return ApiResponse::success($product->toArray(), 'Producto creado correctamente.', 201);
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al crear el producto.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function show(int $id)
    {
        try {
            $product = $this->service->getProductById($id);

            return ApiResponse::success($product->toArray(), 'Producto obtenido correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al obtener el producto.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        try {
            $payload = ProductRequestData::fromArray([
                ...$request->validated(),
                'id' => $id,
            ]);

            $product = $this->service->updateProduct($id, $payload);

            return ApiResponse::success($product->toArray(), 'Producto actualizado correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al actualizar el producto.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(int $id)
    {
        try {
            $response = $this->service->deleteProduct($id);

            return ApiResponse::success($response->toArray(), 'Producto eliminado correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al eliminar el producto.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
