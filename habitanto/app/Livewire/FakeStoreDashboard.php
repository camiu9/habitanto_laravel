<?php

namespace App\Livewire;

use App\Data\FakeStore\Requests\CartRequestData;
use App\Data\FakeStore\Requests\ProductRequestData;
use App\Exceptions\FakeStoreException;
use App\Services\FakeStore\ExternalServicesFacade;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

class FakeStoreDashboard extends Component
{
    private const PRODUCT_UPSERTS_SESSION_KEY = 'fake_store.products.upserts';

    private const PRODUCT_DELETIONS_SESSION_KEY = 'fake_store.products.deleted';

    public string $activeSection = 'products';

    public array $products = [];

    public array $carts = [];

    public ?array $selectedProduct = null;

    public ?array $selectedCart = null;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public string $productLookupId = '';

    public string $cartLookupId = '';

    public array $productForm = [];

    public array $cartForm = [];

    private ExternalServicesFacade $externalServices;

    public function boot(ExternalServicesFacade $externalServices): void
    {
        $this->externalServices = $externalServices;
    }

    public function mount(): void
    {
        $this->resetProductForm();
        $this->resetCartForm();
        $this->refreshDashboard();
    }

    public function setSection(string $section): void
    {
        if (! in_array($section, ['products', 'carts'], true)) {
            return;
        }

        $this->activeSection = $section;
        $this->clearMessages();
    }

    public function refreshDashboard(): void
    {
        $this->clearMessages();
        $snapshot = $this->externalServices->getDashboardSnapshot();
        $this->products = array_map(fn ($product): array => $product->toArray(), $snapshot['products']);
        $this->carts = array_map(fn ($cart): array => $cart->toArray(), $snapshot['carts']);
        $this->syncProductsFromSession();

        if ($snapshot['errors'] !== []) {
            $this->errorMessage = implode(' ', $snapshot['errors']);
        }
    }

    public function findProduct(): void
    {
        $this->clearMessages();
        $this->validate([
            'productLookupId' => ['required', 'integer', 'min:1'],
        ]);

        if ($product = $this->findProductInSession((int) $this->productLookupId)) {
            $this->selectedProduct = $product;
            $this->fillProductForm($this->selectedProduct);
            $this->successMessage = 'Producto local cargado en el formulario.';

            return;
        }

        try {
            $product = $this->externalServices->getProductById((int) $this->productLookupId);
            $this->selectedProduct = $product->toArray();
            $this->fillProductForm($this->selectedProduct);
            $this->successMessage = 'Producto cargado en el formulario.';
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->errorMessage = 'No fue posible consultar el producto solicitado.';
        }
    }

    public function saveProduct(): void
    {
        $this->clearMessages();
        $this->validate($this->productRules());

        try {
            $payload = ProductRequestData::fromArray($this->productForm);
            $isCreating = empty($this->productForm['id']);

            $product = $isCreating
                ? $this->externalServices->createProduct($payload)
                : $this->externalServices->updateProduct((int) $this->productForm['id'], $payload);

            $this->selectedProduct = $this->normalizeProductForLocalState(
                $product->toArray(),
                $isCreating ? null : (int) $this->productForm['id'],
            );
            $this->fillProductForm($this->selectedProduct);
            $this->upsertProductInCollection($this->selectedProduct, $isCreating);
            $this->successMessage = $isCreating
                ? 'Producto creado correctamente.'
                : 'Producto actualizado correctamente.';
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->errorMessage = 'No fue posible guardar el producto.';
        }
    }

    public function editProduct(int $id): void
    {
        $this->productLookupId = (string) $id;
        $this->findProduct();
    }

    public function deleteProduct(int $id): void
    {
        $this->clearMessages();

        try {
            $this->externalServices->deleteProduct($id);
            $this->removeProductFromCollection($id);

            if ((int) ($this->productForm['id'] ?? 0) === $id) {
                $this->resetProductForm();
                $this->selectedProduct = null;
            }

            $this->successMessage = 'Producto eliminado correctamente.';
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->errorMessage = 'No fue posible eliminar el producto.';
        }
    }

    public function newProduct(): void
    {
        $this->resetValidation();
        $this->clearMessages();
        $this->productLookupId = '';
        $this->selectedProduct = null;
        $this->resetProductForm();
    }

    public function findCart(): void
    {
        $this->clearMessages();
        $this->validate([
            'cartLookupId' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $cart = $this->externalServices->getCartById((int) $this->cartLookupId);
            $this->selectedCart = $cart->toArray();
            $this->fillCartForm($this->selectedCart);
            $this->successMessage = 'Carrito cargado en el formulario.';
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->errorMessage = 'No fue posible consultar el carrito solicitado.';
        }
    }

    public function saveCart(): void
    {
        $this->clearMessages();
        $this->normalizeCartForm();
        $this->validate($this->cartRules());

        try {
            $payload = CartRequestData::fromArray($this->cartForm);
            $isCreating = empty($this->cartForm['id']);

            $cart = $isCreating
                ? $this->externalServices->createCart($payload)
                : $this->externalServices->updateCart((int) $this->cartForm['id'], $payload);

            $this->selectedCart = $cart->toArray();
            $this->fillCartForm($this->selectedCart);
            $this->upsertCartInCollection($this->selectedCart, $isCreating);
            $this->successMessage = $isCreating
                ? 'Carrito creado correctamente.'
                : 'Carrito actualizado correctamente.';
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->errorMessage = 'No fue posible guardar el carrito.';
        }
    }

    public function editCart(int $id): void
    {
        $this->cartLookupId = (string) $id;
        $this->findCart();
    }

    public function deleteCart(int $id): void
    {
        $this->clearMessages();

        try {
            $this->externalServices->deleteCart($id);
            $this->removeCartFromCollection($id);

            if ((int) ($this->cartForm['id'] ?? 0) === $id) {
                $this->resetCartForm();
                $this->selectedCart = null;
            }

            $this->successMessage = 'Carrito eliminado correctamente.';
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->errorMessage = 'No fue posible eliminar el carrito.';
        }
    }

    public function newCart(): void
    {
        $this->resetValidation();
        $this->clearMessages();
        $this->cartLookupId = '';
        $this->selectedCart = null;
        $this->resetCartForm();
    }

    public function addCartProductRow(): void
    {
        $this->cartForm['products'][] = $this->emptyCartProduct();
    }

    public function removeCartProductRow(int $index): void
    {
        if (! isset($this->cartForm['products'][$index])) {
            return;
        }

        unset($this->cartForm['products'][$index]);
        $this->cartForm['products'] = array_values($this->cartForm['products']);

        if ($this->cartForm['products'] === []) {
            $this->cartForm['products'][] = $this->emptyCartProduct();
        }
    }

    public function render(): View
    {
        return view('livewire.fake-store-dashboard');
    }

    protected function productRules(): array
    {
        return [
            'productForm.id' => ['nullable', 'integer', 'min:1'],
            'productForm.title' => ['required', 'string', 'max:255'],
            'productForm.price' => ['required', 'numeric', 'min:0'],
            'productForm.description' => ['required', 'string'],
            'productForm.category' => ['required', 'string', 'max:255'],
            'productForm.image' => ['required', 'url', 'max:2048'],
        ];
    }

    protected function cartRules(): array
    {
        return [
            'cartForm.id' => ['nullable', 'integer', 'min:1'],
            'cartForm.userId' => ['required', 'integer', 'min:1'],
            'cartForm.products' => ['required', 'array', 'min:1'],
            'cartForm.products.*.id' => ['required', 'integer', 'min:1'],
            'cartForm.products.*.title' => ['required', 'string', 'max:255'],
            'cartForm.products.*.price' => ['required', 'numeric', 'min:0'],
            'cartForm.products.*.description' => ['required', 'string'],
            'cartForm.products.*.category' => ['required', 'string', 'max:255'],
            'cartForm.products.*.image' => ['required', 'url', 'max:2048'],
            'cartForm.products.*.quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    private function refreshProductsOnly(): void
    {
        try {
            $this->products = array_map(
                fn ($product): array => $product->toArray(),
                $this->externalServices->getAllProducts(),
            );
            $this->syncProductsFromSession();
        } catch (FakeStoreException $exception) {
            $this->products = [];
            $this->syncProductsFromSession();
            $this->errorMessage = $exception->getMessage();
        }
    }

    private function refreshCartsOnly(): void
    {
        try {
            $this->carts = array_map(
                fn ($cart): array => $cart->toArray(),
                $this->externalServices->getAllCarts(),
            );
        } catch (FakeStoreException $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }

    private function clearMessages(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    private function resetProductForm(): void
    {
        $this->productForm = [
            'id' => null,
            'title' => '',
            'price' => '',
            'description' => '',
            'category' => '',
            'image' => '',
        ];
    }

    private function fillProductForm(array $product): void
    {
        $this->productForm = [
            'id' => $product['id'] ?? null,
            'title' => (string) ($product['title'] ?? ''),
            'price' => (string) ($product['price'] ?? ''),
            'description' => (string) ($product['description'] ?? ''),
            'category' => (string) ($product['category'] ?? ''),
            'image' => (string) ($product['image'] ?? ''),
        ];
    }

    private function resetCartForm(): void
    {
        $this->cartForm = [
            'id' => null,
            'userId' => '',
            'products' => [$this->emptyCartProduct()],
        ];
    }

    private function fillCartForm(array $cart): void
    {
        $products = array_values(array_map(
            function (array $product): array {
                return [
                    'id' => $product['id'] ?? '',
                    'title' => (string) ($product['title'] ?? ''),
                    'price' => (string) ($product['price'] ?? ''),
                    'description' => (string) ($product['description'] ?? ''),
                    'category' => (string) ($product['category'] ?? ''),
                    'image' => (string) ($product['image'] ?? ''),
                    'quantity' => (string) ($product['quantity'] ?? 1),
                ];
            },
            array_values(array_filter($cart['products'] ?? [], 'is_array')),
        ));

        $this->cartForm = [
            'id' => $cart['id'] ?? null,
            'userId' => (string) ($cart['userId'] ?? ''),
            'products' => $products !== [] ? $products : [$this->emptyCartProduct()],
        ];
    }

    private function emptyCartProduct(): array
    {
        return [
            'id' => '',
            'title' => '',
            'price' => '',
            'description' => '',
            'category' => '',
            'image' => '',
            'quantity' => '1',
        ];
    }

    private function normalizeCartForm(): void
    {
        $this->cartForm['products'] = array_values(array_filter(
            $this->cartForm['products'] ?? [],
            fn (array $product): bool => collect($product)
                ->filter(fn ($value): bool => $value !== null && $value !== '')
                ->isNotEmpty(),
        ));

        if ($this->cartForm['products'] === []) {
            $this->cartForm['products'][] = $this->emptyCartProduct();
        }
    }

    private function upsertProductInCollection(array $product, bool $prepend = false): void
    {
        $productId = (int) ($product['id'] ?? 0);
        $updated = false;

        $this->products = array_values(array_map(function (array $existing) use ($productId, $product, &$updated): array {
            if ((int) ($existing['id'] ?? 0) !== $productId) {
                return $existing;
            }

            $updated = true;

            return $product;
        }, $this->products));

        if (! $updated) {
            if ($prepend) {
                array_unshift($this->products, $product);
            } else {
                $this->products[] = $product;
            }
        }

        $this->persistProductInSession($product);
    }

    private function removeProductFromCollection(int $id): void
    {
        $this->products = array_values(array_filter(
            $this->products,
            fn (array $product): bool => (int) ($product['id'] ?? 0) !== $id,
        ));

        $this->persistProductDeletionInSession($id);
    }

    private function upsertCartInCollection(array $cart, bool $prepend = false): void
    {
        $cartId = (int) ($cart['id'] ?? 0);
        $updated = false;

        $this->carts = array_values(array_map(function (array $existing) use ($cartId, $cart, &$updated): array {
            if ((int) ($existing['id'] ?? 0) !== $cartId) {
                return $existing;
            }

            $updated = true;

            return $cart;
        }, $this->carts));

        if (! $updated) {
            if ($prepend) {
                array_unshift($this->carts, $cart);

                return;
            }

            $this->carts[] = $cart;
        }
    }

    private function removeCartFromCollection(int $id): void
    {
        $this->carts = array_values(array_filter(
            $this->carts,
            fn (array $cart): bool => (int) ($cart['id'] ?? 0) !== $id,
        ));
    }

    private function normalizeProductForLocalState(array $product, ?int $fallbackId = null): array
    {
        $productId = (int) ($product['id'] ?? 0);

        if ($productId > 0) {
            return $product;
        }

        $product['id'] = $fallbackId && $fallbackId > 0
            ? $fallbackId
            : $this->nextAvailableProductId();

        return $product;
    }

    private function nextAvailableProductId(): int
    {
        $ids = [
            0,
            ...array_map(
                fn (array $product): int => (int) ($product['id'] ?? 0),
                $this->products,
            ),
            ...array_map(
                fn (array $product): int => (int) ($product['id'] ?? 0),
                array_values($this->getPersistedProductUpserts()),
            ),
        ];

        $maxId = max($ids);

        return $maxId + 1;
    }

    private function syncProductsFromSession(): void
    {
        $deletedIds = $this->getPersistedProductDeletions();

        $this->products = array_values(array_filter(
            $this->products,
            fn (array $product): bool => ! in_array((int) ($product['id'] ?? 0), $deletedIds, true),
        ));

        foreach ($this->getPersistedProductUpserts() as $product) {
            $this->upsertProductInMemory($product, prepend: true);
        }
    }

    private function persistProductInSession(array $product): void
    {
        $productId = (int) ($product['id'] ?? 0);
        $upserts = $this->getPersistedProductUpserts();
        $upserts[$productId] = $product;

        $deletedIds = array_values(array_filter(
            $this->getPersistedProductDeletions(),
            fn (int $deletedId): bool => $deletedId !== $productId,
        ));

        session()->put(self::PRODUCT_UPSERTS_SESSION_KEY, $upserts);
        session()->put(self::PRODUCT_DELETIONS_SESSION_KEY, $deletedIds);
    }

    private function persistProductDeletionInSession(int $id): void
    {
        $upserts = $this->getPersistedProductUpserts();
        unset($upserts[$id]);

        $deletedIds = $this->getPersistedProductDeletions();

        if (! in_array($id, $deletedIds, true)) {
            $deletedIds[] = $id;
        }

        session()->put(self::PRODUCT_UPSERTS_SESSION_KEY, $upserts);
        session()->put(self::PRODUCT_DELETIONS_SESSION_KEY, array_values($deletedIds));
    }

    private function getPersistedProductUpserts(): array
    {

        $upserts = session()->get(self::PRODUCT_UPSERTS_SESSION_KEY, []);

        return $upserts;
    }

    private function getPersistedProductDeletions(): array
    {

        $deletedIds = session()->get(self::PRODUCT_DELETIONS_SESSION_KEY, []);

        return array_map('intval', $deletedIds);
    }

    private function findProductInSession(int $id): ?array
    {
        $product = $this->getPersistedProductUpserts()[$id] ?? null;

        return is_array($product) ? $product : null;
    }

    private function upsertProductInMemory(array $product, bool $prepend = false): void
    {
        $productId = (int) ($product['id'] ?? 0);
        $updated = false;

        $this->products = array_values(array_map(function (array $existing) use ($productId, $product, &$updated): array {
            if ((int) ($existing['id'] ?? 0) !== $productId) {
                return $existing;
            }

            $updated = true;

            return $product;
        }, $this->products));

        if (! $updated) {
            if ($prepend) {
                array_unshift($this->products, $product);
            } else {
                $this->products[] = $product;
            }
        }
    }
}
