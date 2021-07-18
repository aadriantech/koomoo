<?php
declare(strict_types=1);

namespace App\Interfaces;

interface CacheInterface
{
    public function set(string $name, mixed $value, int|array $expiration): bool;

    public function get(string $name): mixed;

    public function exists(string $name): bool|int;

}
