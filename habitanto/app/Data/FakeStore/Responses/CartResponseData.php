<?php

namespace App\Data\FakeStore\Responses;

readonly class CartResponseData
{
    public function __construct(
        public int $id,
        public int $userId,
        public array $products,
    ) {}

    public static function fromArray(array $data): self
    {
        $products = array_map(
            fn (array $product): CartItemResponseData => CartItemResponseData::fromArray($product),
            array_values(array_filter($data['products'] ?? [], 'is_array')),
        );

        return new self(
            id: (int) ($data['id'] ?? 0),
            userId: (int) ($data['userId'] ?? 0),
            products: $products,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'products' => array_map(
                fn (CartItemResponseData $product): array => $product->toArray(),
                $this->products,
            ),
        ];
    }
}
