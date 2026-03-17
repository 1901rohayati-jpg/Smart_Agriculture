<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class FirebaseService
{
    public function __construct(private readonly Client $http)
    {
    }

    public function getDevice(string $deviceId): ?array
    {
        return $this->get("/devices/{$deviceId}.json");
    }

    public function updateDevice(string $deviceId, array $payload): array
    {
        return $this->patch("/devices/{$deviceId}.json", $payload);
    }

    public function pushHistory(string $deviceId, array $payload): array
    {
        return $this->post("/devices/{$deviceId}/history.json", $payload);
    }

    public function getHistory(string $deviceId): array
    {
        return $this->get("/devices/{$deviceId}/history.json") ?? [];
    }

    private function get(string $path): mixed
    {
        $response = $this->http->get($this->url($path), [
            'timeout' => config('services.firebase.timeout'),
            'query' => $this->query(),
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    private function patch(string $path, array $payload): array
    {
        $response = $this->http->patch($this->url($path), [
            'timeout' => config('services.firebase.timeout'),
            'query' => $this->query(),
            'json' => $payload,
        ]);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    private function post(string $path, array $payload): array
    {
        $response = $this->http->post($this->url($path), [
            'timeout' => config('services.firebase.timeout'),
            'query' => $this->query(),
            'json' => $payload,
        ]);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    private function url(string $path): string
    {
        return config('services.firebase.database_url') . $path;
    }

    private function query(): array
    {
        $query = [];

        if (filled(config('services.firebase.auth_token'))) {
            Arr::set($query, 'auth', config('services.firebase.auth_token'));
        }

        return $query;
    }
}
