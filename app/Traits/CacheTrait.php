<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

trait CacheTrait
{
    /**
     * @param string|null $dataSource Defines the data source when sending logs
     * @param bool $useCache
     * @param string|null $cacheName
     * @param bool $isCached
     * @param int|null $expireInSeconds
     * @param string|null $responseData
     * @param string $cacheType
     */
    public function __construct(
        public ?string $dataSource,
        public bool $useCache = false,
        public ?string $cacheName = null,
        public bool $isCached = false,
        public ?int $expireInSeconds = null,
        public ?string $responseData = null,
        public string $cacheType = 'file'
    )
    {
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
            $dataFromCache = Cache::store($this->cacheType)
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
            Log::info("Used existing cache for $this->dataSource");
        }
    }

    public function getCacheIfExists(): self
    {
        $this->isCached = false;
        if ($this->useCache && Cache::store($this->cacheType)->has($this->cacheName)) {
            $this->isCached = true;
            $this->getCache();
        }

        return $this;
    }

    private function saveToCache(): void
    {
        try {
            if (!Cache::store($this->cacheType)->has($this->cacheName)) {
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

                } else {
                    Log::info("Saved $this->dataSource data to cache");
                }

                // store data to cache
                Cache::put($this->cacheName, $this->responseData, $duration);
            }

        } catch (InvalidArgumentException | Throwable $e) {
            $errorMessage = sprintf('Class: StorageService; Method: cached; Message: %s', $e->getMessage());
            Log::error($errorMessage);
        }
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
            Log::info("Used direct data from resource $this->dataSource");
        }

        return $this;
    }

    public function setCacheType(string $type)
    {
        $this->cacheType = $type;
    }
}
