<?php

declare(strict_types=1);

namespace App\Service;

interface CommissionsStorageInterface
{
    /**
     * @return string
     */
    public function getEuCommissions(): string;

    /**
     * @return string
     */
    public function getNormalCommissions(): string;
}