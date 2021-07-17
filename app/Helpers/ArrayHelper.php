<?php
declare(strict_types=1);

namespace App\Helpers;

class ArrayHelper
{
    public array $data = [];

    /**
     * Converts json values to an array format
     *
     * @param string|null $data
     * @return ArrayHelper
     */
    public function jsonToArray(?string $data): self
    {
        if (!empty($data)) {
            $data = json_decode($data, true);

            $this->data = $data;
        }

        return $this;
    }
}
