<?php
declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CacheInterface;
use Illuminate\Support\Facades\Redis;

final class RedisService implements CacheInterface
{
    public function __construct(public Redis $redis)
    {
    }

    public function set(string $name, mixed $value, int|array $expiration): bool
    {
        return $this->redis::set($name, $value, $expiration);
    }

    public function get(string $name): mixed
    {
        return $this->redis::get($name);
    }

    public function exists(string $name): bool|int
    {
        return $this->redis::exists($name);
    }
}
