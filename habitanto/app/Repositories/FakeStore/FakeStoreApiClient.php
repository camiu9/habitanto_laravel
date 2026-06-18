<?php

namespace App\Repositories\FakeStore;

use App\Exceptions\FakeStoreException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

class FakeStoreApiClient
{
    public function __construct(
        private readonly Factory $http,
    ) {}

    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, $query);
    }

    public function post(string $uri, array $payload): array
    {
        return $this->request('POST', $uri, $payload);
    }

    public function put(string $uri, array $payload): array
    {
        return $this->request('PUT', $uri, $payload);
    }

    public function delete(string $uri): array
    {
        return $this->request('DELETE', $uri);
    }

    private function pendingRequest(): PendingRequest
    {
        $request = $this->http
            ->baseUrl((string) config('services.fakestore.base_url'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.fakestore.timeout'))
            ->connectTimeout((int) config('services.fakestore.connect_timeout'));

        $forceIpResolve = (string) config('services.fakestore.force_ip_resolve');

        if ($forceIpResolve !== '') {
            $request = $request->withOptions([
                'force_ip_resolve' => $forceIpResolve,
            ]);
        }

        return $request;
    }

    private function request(string $method, string $uri, array $payload = []): array
    {
        try {
            $response = match ($method) {
                'GET' => $this->pendingRequest()->get($uri, $payload),
                'POST' => $this->pendingRequest()->post($uri, $payload),
                'PUT' => $this->pendingRequest()->put($uri, $payload),
                'DELETE' => $this->pendingRequest()->delete($uri),
                default => throw FakeStoreException::invalidResponse($uri, ['method' => $method]),
            };
        } catch (ConnectionException $exception) {
            throw FakeStoreException::connection($uri, $exception, ['method' => $method]);
        }

        if ($response->failed()) {
            throw FakeStoreException::upstream($uri, $response->status(), [
                'method' => $method,
                'body' => $response->json() ?? $response->body(),
            ]);
        }

        $json = $response->json();

        if ($json === null && $response->body() === '') {
            return [];
        }

        if (! is_array($json)) {
            throw FakeStoreException::invalidResponse($uri, ['method' => $method, 'body' => $response->body()]);
        }

        return $json;
    }
}
