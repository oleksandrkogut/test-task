<?php

declare(strict_types=1);

namespace App\Service\Binlist;

interface BinlistSettingsStorageInterface
{
    /**
     * @return string
     */
    public function getBinlistHost(): string;
}