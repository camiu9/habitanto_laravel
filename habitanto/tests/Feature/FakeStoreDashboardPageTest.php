<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FakeStoreDashboardPageTest extends TestCase
{
    public function test_dashboard_page_renders_with_fake_data(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([
                [
                    'id' => 1,
                    'title' => 'Mountain Bottle',
                    'price' => 18.50,
                    'description' => 'Hydration for hikes',
                    'category' => 'outdoor',
                    'image' => 'https://example.com/bottle.png',
                ],
            ], 200),
            'https://fakestoreapi.com/carts' => Http::response([
                [
                    'id' => 3,
                    'userId' => 21,
                    'products' => [
                        [
                            'id' => 1,
                            'title' => 'Mountain Bottle',
                            'price' => 18.50,
                            'description' => 'Hydration for hikes',
                            'category' => 'outdoor',
                            'image' => 'https://example.com/bottle.png',
                            'quantity' => 1,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get('/fake-store');

        $response
            ->assertOk()
            ->assertSee('Centro de integracion')
            ->assertSee('Mountain Bottle')
            ->assertSee('Carritos cargados');
    }
}
