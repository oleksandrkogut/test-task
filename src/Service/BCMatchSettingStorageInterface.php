<?php

declare(strict_types=1);

namespace App\Service;

interface BCMatchSettingStorageInterface
{
    /**
     * @return int
     */
    public function getBcMatchScale(): int;
}