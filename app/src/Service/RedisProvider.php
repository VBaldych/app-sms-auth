<?php

declare(strict_types=1);

namespace App\Service;

use Predis\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RedisProvider
{
    private readonly Client $client;

    public function __construct(string $redisUrl)
    {
        $this->client = new Client($redisUrl);
    }

    public function set(string $key, mixed $value, int $lifetime = 0): void
    {
        $this->tryCallback(function () use ($key, $value, $lifetime): void {
            $this->client->set($key, $value);
            if ($lifetime > 0) {
                $this->client->expire($key, $lifetime);
            }
        });
    }

    public function get(string $key): ?array
    {
        $cacheData = $this->tryCallback(fn() => $this->client->get($key));

        return ($cacheData)
            ? json_decode((string) $cacheData, true, 512, JSON_THROW_ON_ERROR)
            : null;
    }

    public function delete(string $key): void
    {
        $this->tryCallback(function () use ($key): void {
            $this->client->del($key);
        });
    }

    protected function tryCallback(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Exception $exception) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $exception->getMessage());
        }
    }
}