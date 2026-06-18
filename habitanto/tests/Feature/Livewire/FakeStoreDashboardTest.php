<?php

namespace Tests\Feature\Livewire;

use App\Livewire\FakeStoreDashboard;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class FakeStoreDashboardTest extends TestCase
{
    public function test_created_product_is_added_to_live_collection_even_if_api_returns_zero_id(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::sequence()
                ->push([
                    [
                        'id' => 1,
                        'title' => 'Existing product',
                        'price' => 19.99,
                        'description' => 'Existing description',
                        'category' => 'general',
                        'image' => 'https://example.com/existing.png',
                    ],
                ], 200)
                ->push([
                    'id' => 0,
                    'title' => 'Nuevo producto',
                    'price' => 49.99,
                    'description' => 'Descripcion del nuevo producto',
                    'category' => 'tech',
                    'image' => 'https://example.com/new.png',
                ], 200),
            'https://fakestoreapi.com/carts' => Http::response([], 200),
        ]);

        Livewire::test(FakeStoreDashboard::class)
            ->set('productForm.title', 'Nuevo producto')
            ->set('productForm.price', '49.99')
            ->set('productForm.description', 'Descripcion del nuevo producto')
            ->set('productForm.category', 'tech')
            ->set('productForm.image', 'https://example.com/new.png')
            ->call('saveProduct')
            ->assertSet('products.0.title', 'Nuevo producto')
            ->assertSet('products.0.id', 2)
            ->assertSet('selectedProduct.title', 'Nuevo producto');
    }
}
