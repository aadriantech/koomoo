<?php
declare(strict_types=1);

namespace App\Interfaces;

interface StorageInterface
{
    public function setUrl(string $url): self;

    public function setAction(string $action): self;

    public function useCache(string $name, int $expireInSeconds = 3600): self;

    public function getCacheIfExists(): self;

    public function sendRequest(): self;

    public function getErrorMessages(): ?array;

    public function toArray(): array;
}
