<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\ArrayHelper;
use App\Interfaces\DataInterface;
use App\Interfaces\StorageInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

final class StorageService implements StorageInterface
{
    public const FILE_CACHE  = 'file';
    public const REDIS_CACHE = 'redis';

    /**
     * @param DataInterface $data Dependency Injection (Mysql, Mongo, Postgress etc)
     * @param ArrayHelper $arrayHelper
     * @param bool $useCache
     * @param array|null $errorMessages
     * @param string|null $url
     * @param string|null $action
     * @param string|null $cacheName
     * @param bool $isCached
     * @param int|null $expireInSeconds
     * @param string|null $responseData
     */
    public function __construct(
        private DataInterface $data,
        private ArrayHelper $arrayHelper,
        private bool $useCache = false,
        private ?array $errorMessages = null,
        public ?string $url = null,
        public ?string $action = null,
        public ?string $cacheName = null,
        public bool $isCached = false,
        public ?int $expireInSeconds = null,
        public ?string $responseData = null
    )
    {
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function useCache(string $name, int $expireInSeconds = 3600): self
    {
        $this->cacheName = $name;
        $this->useCache  = true;

        if (!empty($expireInSeconds)) {
            $this->expireInSeconds = $expireInSeconds;
        }

        return $this;
    }

    private function getCache(): void
    {
        try {
            $dataFromCache = Cache::store(self::FILE_CACHE)
                ->get($this->cacheName);

        } catch (InvalidArgumentException $e) {
            $errorMessage = sprintf(
                'Class: StorageService; Method: getCache; Message: %s',
                $e->getMessage()
            );

            Log::error($errorMessage);
        }

        if (!empty($dataFromCache)) {
            $this->responseData = $dataFromCache;
            Log::info("Used existing cache for $this->url/$this->action");
        }
    }

    public function getCacheIfExists(): self
    {
        $this->isCached = false;
        if ($this->useCache && Cache::store(self::FILE_CACHE)->has($this->cacheName)) {
            $this->isCached = true;
            $this->getCache();
        }

        return $this;
    }

    private function getDataFromResource(): self
    {
        $this->data->setGuzzleCustomErrorMessage(null);
        $this->responseData = $this->data->get($this->url, $this->action);

        $errorMessage = $this->data->getErrorMessage();
        if (null !== $errorMessage) {
            $this->errorMessages[] = $errorMessage;
        }

        return $this;
    }

    private function saveToCache(): self
    {
        try {
            if (!Cache::store(self::FILE_CACHE)->has($this->cacheName)) {
                // set expiration value
                $duration = now()->addMonth();
                if (null !== $this->expireInSeconds) {
                    $duration = now()->addSeconds($this->expireInSeconds);
                }

                if (empty($this->responseData)) {
                    $warningMessage = sprintf('Class: %s; Method: %s; Message: %s',
                        'StorageService',
                        'cached',
                        'There is nothing to cache, data returned empty or has exception'
                    );
                    Log::warning($warningMessage);

                    return $this;

                } else {
                    Log::info("Saved $this->url/$this->action data to cache");
                }

                // store data to cache
                Cache::put($this->cacheName, $this->responseData, $duration);
            }

        } catch (InvalidArgumentException | Throwable $e) {
            $errorMessage = sprintf('Class: StorageService; Method: cached; Message: %s', $e->getMessage());
            Log::error($errorMessage);
        }

        return $this;
    }

    public function cacheIfNotExist(): self
    {
        if ($this->useCache && !$this->isCached) {
            $this->getDataFromResource();
            $this->saveToCache();
        }

        return $this;
    }

    public function getDirectData(): self
    {
        if (!$this->useCache) {
            $this->getDataFromResource();
            Log::info("Used direct data from resource $this->url/$this->action");
        }

        return $this;
    }

    public function sendRequest(): self
    {
        $this->getCacheIfExists()
            ->cacheIfNotExist()
            ->getDirectData();

        return $this;
    }

    public function toArray(): array
    {
        return $this->arrayHelper
            ->jsonToArray($this->responseData)
            ->data;
    }

    public function getErrorMessages(): ?array
    {
        return $this->errorMessages;
    }
}
