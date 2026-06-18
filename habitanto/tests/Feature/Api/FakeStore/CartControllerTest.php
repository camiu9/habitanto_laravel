<?php

namespace Tests\Feature\Api\FakeStore;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    public function test_it_creates_a_cart(): void
    {
        Http::fake([
            'https://fakestoreapi.com/carts' => Http::response([
                'id' => 7,
                'userId' => 15,
                'products' => [
                    [
                        'id' => 4,
                        'title' => 'Desk Lamp',
                        'price' => 40.50,
                        'description' => 'Warm light',
                        'category' => 'home',
                        'image' => 'https://example.com/lamp.png',
                        'quantity' => 2,
                    ],
                ],
            ], 200),
        ]);

        $payload = [
            'userId' => 15,
            'products' => [
                [
                    'id' => 4,
                    'title' => 'Desk Lamp',
                    'price' => 40.50,
                    'description' => 'Warm light',
                    'category' => 'home',
                    'image' => 'https://example.com/lamp.png',
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/fake-store/carts', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.userId', 15)
            ->assertJsonPath('data.products.0.quantity', 2);
    }
}
