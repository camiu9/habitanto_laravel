<?php

namespace Tests\Feature\Architecture;

use Tests\TestCase;

class LivewireWrapperBoundaryTest extends TestCase
{
    public function test_livewire_uses_the_unified_wrapper_instead_of_individual_services_or_http_clients(): void
    {
        $source = file_get_contents(app_path('Livewire/FakeStoreDashboard.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('ExternalServicesFacade', $source);
        $this->assertStringNotContainsString('ProductService::class', $source);
        $this->assertStringNotContainsString('CartService::class', $source);
        $this->assertStringNotContainsString('Http::', $source);
        $this->assertStringNotContainsString('FakeStoreApiClient', $source);
    }
}
