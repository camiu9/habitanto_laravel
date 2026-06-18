<?php

namespace App\Support\FakeStore;

use App\Exceptions\FakeStoreException;

class FakeStoreResponseValidator
{
    public function assertProductCollection(array $products, string $resource = '/products'): void
    {
        foreach ($products as $index => $product) {
            if (! is_array($product)) {
                throw FakeStoreException::invalidResponse($resource, [
                    'index' => $index,
                    'reason' => 'Product item is not an array.',
                ]);
            }

            $this->assertProduct($product, "{$resource}[{$index}]");
        }
    }

    public function assertProduct(array $product, string $resource): void
    {
        $missingKeys = array_values(array_filter(
            ['id', 'title', 'price', 'description', 'category', 'image'],
            fn (string $key): bool => ! array_key_exists($key, $product),
        ));

        if ($missingKeys !== []) {
            throw FakeStoreException::invalidResponse($resource, [
                'missing_keys' => $missingKeys,
            ]);
        }
    }

    public function assertCartCollection(array $carts, string $resource = '/carts'): void
    {
        foreach ($carts as $index => $cart) {
            if (! is_array($cart)) {
                throw FakeStoreException::invalidResponse($resource, [
                    'index' => $index,
                    'reason' => 'Cart item is not an array.',
                ]);
            }

            $this->assertCart($cart, "{$resource}[{$index}]");
        }
    }

    public function assertCart(array $cart, string $resource): void
    {
        $missingKeys = array_values(array_filter(
            ['id', 'userId', 'products'],
            fn (string $key): bool => ! array_key_exists($key, $cart),
        ));

        if ($missingKeys !== []) {
            throw FakeStoreException::invalidResponse($resource, [
                'missing_keys' => $missingKeys,
            ]);
        }

        if (! is_array($cart['products'])) {
            throw FakeStoreException::invalidResponse($resource, [
                'reason' => 'products must be an array.',
            ]);
        }
    }
}
