<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\ArrayHelper;
use App\Interfaces\CacheInterface;
use App\Interfaces\DataInterface;
use App\Interfaces\StorageInterface;
use App\Traits\CacheTrait;

final class StorageService implements StorageInterface
{
    use CacheTrait;

    /**
     * @param DataInterface $data Dependency Injection (Mysql, Mongo, Postgress etc)
     * @param ArrayHelper $arrayHelper
     * @param CacheInterface $cacheType
     * @param string|null $url
     * @param string|null $action
     * @param array|null $errorMessages
     */
    public function __construct(
        protected DataInterface $data,
        protected ArrayHelper $arrayHelper,
        public CacheInterface $cacheType,
        public ?string $url = null,
        public ?string $action = null,
        protected ?array $errorMessages = null
    )
    {
        $this->setCacheType($this->cacheType);
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

    public function setDataSource(string $dataSource): self
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    public function cache(string $name, int $expireInSeconds = 3600): self
    {
        $this->useCache($name, $expireInSeconds);

        return $this;
    }

    public function sendRequest(): self
    {
        $this->getCacheIfExists()
            ->cacheIfNotExist()
            ->getDirectData();

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
