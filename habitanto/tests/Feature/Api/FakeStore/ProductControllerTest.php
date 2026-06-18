<?php

namespace Tests\Feature\Api\FakeStore;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    public function test_it_returns_the_products_collection(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([
                [
                    'id' => 1,
                    'title' => 'Backpack',
                    'price' => 99.99,
                    'description' => 'Travel backpack',
                    'category' => 'travel',
                    'image' => 'https://example.com/backpack.png',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/fake-store/products');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.title', 'Backpack');
    }

    public function test_it_returns_a_managed_error_when_upstream_fails(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([
                'message' => 'upstream failure',
            ], 500),
        ]);

        $response = $this->getJson('/api/fake-store/products');

        $response
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Fake Store API devolvio un error al procesar /products.');
    }
}
