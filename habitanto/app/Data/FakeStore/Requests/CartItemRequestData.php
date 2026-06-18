<?php

namespace App\Data\FakeStore\Requests;

readonly class CartItemRequestData
{
    public function __construct(
        public int $id,
        public string $title,
        public float $price,
        public string $description,
        public string $category,
        public string $image,
        public int $quantity = 1,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            price: (float) ($data['price'] ?? 0),
            description: (string) ($data['description'] ?? ''),
            category: (string) ($data['category'] ?? ''),
            image: (string) ($data['image'] ?? ''),
            quantity: max(1, (int) ($data['quantity'] ?? 1)),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'category' => $this->category,
            'image' => $this->image,
            'quantity' => $this->quantity,
        ];
    }
}
