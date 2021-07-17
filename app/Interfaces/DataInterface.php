<?php
declare(strict_types=1);

namespace App\Interfaces;

use GuzzleHttp\Exception\GuzzleException;

interface DataInterface
{
    public function getAll(string $url): string;

    public function get(string $url, ?string $action = null): ?string;

    public function getErrorMessage(): ?string;

    public function setGuzzleCustomErrorMessage(GuzzleException $exception): void;
}
