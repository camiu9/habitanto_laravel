<?php

namespace App\Repositories\FakeStore;

use App\Data\FakeStore\Requests\CartRequestData;
use App\Data\FakeStore\Responses\CartResponseData;
use App\Support\FakeStore\FakeStoreResponseValidator;

class CartRepository
{
    public function __construct(
        private readonly FakeStoreApiClient $client,
        private readonly FakeStoreResponseValidator $validator,
    ) {}

    public function all(): array
    {
        $carts = $this->client->get('/carts');
        $this->validator->assertCartCollection($carts);

        return array_map(
            fn (array $cart): CartResponseData => CartResponseData::fromArray($cart),
            array_values(array_filter($carts, 'is_array')),
        );
    }

    public function create(CartRequestData $request): CartResponseData
    {
        $cart = $this->client->post('/carts', $request->toArray());
        $this->validator->assertCart($cart, '/carts');

        return CartResponseData::fromArray($cart);
    }

    public function find(int $id): CartResponseData
    {
        $cart = $this->client->get("/carts/{$id}");
        $this->validator->assertCart($cart, "/carts/{$id}");

        return CartResponseData::fromArray($cart);
    }

    public function update(int $id, CartRequestData $request): CartResponseData
    {
        $cart = $this->client->put("/carts/{$id}", $request->toArray());
        $this->validator->assertCart($cart, "/carts/{$id}");

        return CartResponseData::fromArray($cart);
    }

    public function delete(int $id): array
    {
        return $this->client->delete("/carts/{$id}");
    }
}
