<?php
declare(strict_types=1);

namespace App\Traits;

use App\Interfaces\CacheInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

trait CacheTrait
{
    /**
     * @param string|null $dataSource Defines the data source when sending logs
     * @param CacheInterface $cacheType
     * @param bool $useCache
     * @param string|null $cacheName
     * @param bool $isCached
     * @param int|null $expireInSeconds
     * @param string|null $responseData
     */
    public function __construct(
        public ?string $dataSource,
        public CacheInterface $cacheType,
        public bool $useCache = false,
        public ?string $cacheName = null,
        public bool $isCached = false,
        public ?int $expireInSeconds = null,
        public ?string $responseData = null
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
            $dataFromCache = $this->cacheType->get($this->cacheName);

        } catch (Throwable $e) {
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
        if ($this->useCache && $this->cacheType->exists($this->cacheName)) {
            $this->isCached = true;
            $this->getCache();
        }

        return $this;
    }

    private function saveToCache(): void
    {
        try {
            if (!$this->cacheType->exists($this->cacheName)) {
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
                $this->cacheType->set($this->cacheName, $this->responseData, $duration->second);
            }

        } catch (Throwable $e) {
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

    public function setCacheType(CacheInterface $cacheType): void
    {
        $this->cacheType = $cacheType;
    }
}
