<?php

namespace App\Http\Controllers\Api\FakeStore;

use App\Data\FakeStore\Requests\CartRequestData;
use App\Exceptions\FakeStoreException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FakeStore\StoreCartRequest;
use App\Http\Requests\FakeStore\UpdateCartRequest;
use App\Services\FakeStore\ExternalServicesFacade;
use App\Support\FakeStore\ApiResponse;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        private readonly ExternalServicesFacade $service,
    ) {}

    public function index()
    {
        try {
            $carts = array_map(
                fn ($cart): array => $cart->toArray(),
                $this->service->getAllCarts(),
            );

            return ApiResponse::success($carts, 'Carritos obtenidos correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al consultar carritos.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function store(StoreCartRequest $request)
    {
        try {
            $cart = $this->service->createCart(CartRequestData::fromArray($request->validated()));

            return ApiResponse::success($cart->toArray(), 'Carrito creado correctamente.', 201);
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al crear el carrito.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function show(int $id)
    {
        try {
            $cart = $this->service->getCartById($id);

            return ApiResponse::success($cart->toArray(), 'Carrito obtenido correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al obtener el carrito.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function update(UpdateCartRequest $request, int $id)
    {
        try {
            $payload = CartRequestData::fromArray([
                ...$request->validated(),
                'id' => $id,
            ]);

            $cart = $this->service->updateCart($id, $payload);

            return ApiResponse::success($cart->toArray(), 'Carrito actualizado correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al actualizar el carrito.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(int $id)
    {
        try {
            $response = $this->service->deleteCart($id);

            return ApiResponse::success($response->toArray(), 'Carrito eliminado correctamente.');
        } catch (FakeStoreException $exception) {
            return ApiResponse::error($exception->getMessage(), $exception->status, $exception->context);
        } catch (Throwable $exception) {
            return ApiResponse::error('Ocurrio un error inesperado al eliminar el carrito.', 500, [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
