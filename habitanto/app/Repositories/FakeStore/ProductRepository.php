<?php

namespace App\Repositories\FakeStore;

use App\Data\FakeStore\Requests\ProductRequestData;
use App\Data\FakeStore\Responses\ProductResponseData;
use App\Support\FakeStore\FakeStoreResponseValidator;

class ProductRepository
{
    public function __construct(
        private readonly FakeStoreApiClient $client,
        private readonly FakeStoreResponseValidator $validator,
    ) {}

    public function all(): array
    {
        $products = $this->client->get('/products');
        $this->validator->assertProductCollection($products);

        return array_map(
            fn (array $product): ProductResponseData => ProductResponseData::fromArray($product),
            array_values(array_filter($products, 'is_array')),
        );
    }

    public function create(ProductRequestData $request): ProductResponseData
    {
        $product = $this->client->post('/products', $request->toArray());
        $this->validator->assertProduct($product, '/products');

        return ProductResponseData::fromArray($product);
    }

    public function find(int $id): ProductResponseData
    {
        $product = $this->client->get("/products/{$id}");
        $this->validator->assertProduct($product, "/products/{$id}");

        return ProductResponseData::fromArray($product);
    }

    public function update(int $id, ProductRequestData $request): ProductResponseData
    {
        $product = $this->client->put("/products/{$id}", $request->toArray());
        $this->validator->assertProduct($product, "/products/{$id}");

        return ProductResponseData::fromArray($product);
    }

    public function delete(int $id): array
    {
        return $this->client->delete("/products/{$id}");
    }
}
