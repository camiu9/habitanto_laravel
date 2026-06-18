<?php

namespace Tests\Feature\Services\FakeStore;

use App\Data\FakeStore\Requests\ProductRequestData;
use App\Data\FakeStore\Responses\ProductResponseData;
use App\Exceptions\FakeStoreException;
use App\Services\FakeStore\ExternalServicesFacade;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalServicesFacadeTest extends TestCase
{
    public function test_it_returns_products_through_the_unified_wrapper_contract(): void
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

        $products = app(ExternalServicesFacade::class)->getAllProducts();

        $this->assertCount(1, $products);
        $this->assertContainsOnlyInstancesOf(ProductResponseData::class, $products);
        $this->assertSame('Backpack', $products[0]->title);
    }

    public function test_it_normalizes_an_external_http_error(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([
                'message' => 'forbidden',
            ], 403),
        ]);

        $this->expectException(FakeStoreException::class);
        $this->expectExceptionMessage('Fake Store API no autorizo el acceso a /products.');

        app(ExternalServicesFacade::class)->getAllProducts();
    }

    public function test_it_normalizes_a_timeout(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::failedConnection('cURL error 28: Operation timed out after 5000 milliseconds'),
        ]);

        try {
            app(ExternalServicesFacade::class)->getAllProducts();
            $this->fail('Expected FakeStoreException was not thrown.');
        } catch (FakeStoreException $exception) {
            $this->assertSame(504, $exception->status);
            $this->assertSame('Fake Store API tardo demasiado en responder para /products.', $exception->getMessage());
        }
    }

    public function test_it_rejects_an_invalid_non_array_response(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response('plain text body', 200),
        ]);

        try {
            app(ExternalServicesFacade::class)->getAllProducts();
            $this->fail('Expected FakeStoreException was not thrown.');
        } catch (FakeStoreException $exception) {
            $this->assertSame(502, $exception->status);
            $this->assertSame('La respuesta de Fake Store API para /products no tiene el formato esperado.', $exception->getMessage());
        }
    }

    public function test_it_rejects_incomplete_product_data(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([
                [
                    'id' => 1,
                    'price' => 15.00,
                    'description' => 'Incomplete product',
                    'category' => 'general',
                    'image' => 'https://example.com/item.png',
                ],
            ], 200),
        ]);

        try {
            app(ExternalServicesFacade::class)->getAllProducts();
            $this->fail('Expected FakeStoreException was not thrown.');
        } catch (FakeStoreException $exception) {
            $this->assertSame(502, $exception->status);
            $this->assertSame('La respuesta de Fake Store API para /products[0] no tiene el formato esperado.', $exception->getMessage());
            $this->assertSame(['title'], $exception->context['missing_keys']);
        }
    }

    public function test_it_keeps_create_product_contract(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([
                'id' => 10,
                'title' => 'Keyboard',
                'price' => 59.99,
                'description' => 'Mechanical keyboard',
                'category' => 'tech',
                'image' => 'https://example.com/keyboard.png',
            ], 201),
        ]);

        $product = app(ExternalServicesFacade::class)->createProduct(new ProductRequestData(
            id: null,
            title: 'Keyboard',
            price: 59.99,
            description: 'Mechanical keyboard',
            category: 'tech',
            image: 'https://example.com/keyboard.png',
        ));

        $this->assertInstanceOf(ProductResponseData::class, $product);
        $this->assertSame(10, $product->id);
    }
}
