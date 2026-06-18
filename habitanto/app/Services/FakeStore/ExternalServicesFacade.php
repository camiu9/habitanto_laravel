<?php

namespace App\Services\FakeStore;

use App\Data\FakeStore\Requests\CartRequestData;
use App\Data\FakeStore\Requests\ProductRequestData;
use App\Data\FakeStore\Responses\CartResponseData;
use App\Data\FakeStore\Responses\DeleteResponseData;
use App\Data\FakeStore\Responses\ProductResponseData;
use App\Exceptions\FakeStoreException;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalServicesFacade
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly CartService $cartService,
    ) {}

    public function getDashboardSnapshot(): array
    {
        $products = [];
        $carts = [];
        $errors = [];

        try {
            $products = $this->productService->getAll();
        } catch (FakeStoreException $exception) {
            $errors[] = $this->report($exception, 'products.index');
        } catch (Throwable $exception) {
            $errors[] = $this->report(FakeStoreException::unexpected('/products', $exception), 'products.index');
        }

        try {
            $carts = $this->cartService->getAll();
        } catch (FakeStoreException $exception) {
            $errors[] = $this->report($exception, 'carts.index');
        } catch (Throwable $exception) {
            $errors[] = $this->report(FakeStoreException::unexpected('/carts', $exception), 'carts.index');
        }

        return [
            'products' => $products,
            'carts' => $carts,
            'errors' => $errors,
        ];
    }

    public function getAllProducts(): array
    {
        return $this->execute('/products', 'products.index', fn (): array => $this->productService->getAll());
    }

    public function createProduct(ProductRequestData $request): ProductResponseData
    {
        return $this->execute('/products', 'products.store', fn (): ProductResponseData => $this->productService->create($request));
    }

    public function getProductById(int $id): ProductResponseData
    {
        return $this->execute("/products/{$id}", 'products.show', fn (): ProductResponseData => $this->productService->getById($id));
    }

    public function updateProduct(int $id, ProductRequestData $request): ProductResponseData
    {
        return $this->execute("/products/{$id}", 'products.update', fn (): ProductResponseData => $this->productService->update($id, $request));
    }

    public function deleteProduct(int $id): DeleteResponseData
    {
        return $this->execute("/products/{$id}", 'products.destroy', fn (): DeleteResponseData => $this->productService->delete($id));
    }

    public function getAllCarts(): array
    {
        return $this->execute('/carts', 'carts.index', fn (): array => $this->cartService->getAll());
    }

    public function createCart(CartRequestData $request): CartResponseData
    {
        return $this->execute('/carts', 'carts.store', fn (): CartResponseData => $this->cartService->create($request));
    }

    public function getCartById(int $id): CartResponseData
    {
        return $this->execute("/carts/{$id}", 'carts.show', fn (): CartResponseData => $this->cartService->getById($id));
    }

    public function updateCart(int $id, CartRequestData $request): CartResponseData
    {
        return $this->execute("/carts/{$id}", 'carts.update', fn (): CartResponseData => $this->cartService->update($id, $request));
    }

    public function deleteCart(int $id): DeleteResponseData
    {
        return $this->execute("/carts/{$id}", 'carts.destroy', fn (): DeleteResponseData => $this->cartService->delete($id));
    }

    private function execute(string $resource, string $operation, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (FakeStoreException $exception) {
            $this->report($exception, $operation);

            throw $exception;
        } catch (Throwable $exception) {
            $normalized = FakeStoreException::unexpected($resource, $exception);
            $this->report($normalized, $operation);

            throw $normalized;
        }
    }

    private function report(FakeStoreException $exception, string $operation): string
    {
        Log::error('External services facade error', [
            'operation' => $operation,
            'message' => $exception->getMessage(),
            'status' => $exception->status,
            'context' => $exception->context,
        ]);

        return $exception->getMessage();
    }
}
