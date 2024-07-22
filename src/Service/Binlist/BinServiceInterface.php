<?php

declare(strict_types=1);

namespace App\Service\Binlist;

use App\Dto\BinDto;

interface BinServiceInterface
{
    /**
     * @param string $bin
     * @return BinDto
     */
    public function getBinDetails(string $bin): BinDto;
}