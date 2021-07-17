<?php
declare(strict_types=1);

namespace App\Services;

use App\Interfaces\DataInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Log;

final class ApiService implements DataInterface
{
    public function __construct(private Client $client, private ?string $errorMessage = null, public ?string $action = null)
    {
    }

    public function setGuzzleCustomErrorMessage(?GuzzleException $exception): void
    {
        if (null !== $exception && $exception->getCode() === 404) {
            $this->errorMessage = "$this->action Resource not found";

        } elseif (null === $exception) { // resets the property
            $this->errorMessage = null;
        }
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getAll(string $url): string
    {
        return $this->get($url);
    }

    public function get(string $url, ?string $action = null): ?string
    {
        try {

            if (!empty($action)) {
                $this->action = $action;
                $url .= "/$action";
            }

            $response = $this->client->request('GET', $url);

            return $response->getBody()->getContents();

        } catch (GuzzleException $e) {
            $this->setGuzzleCustomErrorMessage($e);
            $errorMessage = sprintf('Class: ApiService; Method: get; Message: %s', $e->getMessage());
            Log::warning($errorMessage);
        }

        return null;
    }
}
