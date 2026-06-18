<?php

namespace App\Data\FakeStore\Requests;

readonly class CartRequestData
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public array $products,
    ) {}

    public static function fromArray(array $data): self
    {
        $products = array_map(
            fn (array $product): CartItemRequestData => CartItemRequestData::fromArray($product),
            array_values(array_filter($data['products'] ?? [], 'is_array')),
        );

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) ($data['userId'] ?? 0),
            products: $products,
        );
    }

    public function toArray(): array
    {
        $payload = [
            'userId' => $this->userId,
            'products' => array_map(
                fn (CartItemRequestData $product): array => $product->toArray(),
                $this->products,
            ),
        ];

        if ($this->id !== null) {
            $payload['id'] = $this->id;
        }

        return $payload;
    }
}
