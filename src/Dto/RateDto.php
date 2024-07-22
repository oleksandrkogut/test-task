<?php

declare(strict_types=1);

namespace App\Dto;

use PrinsFrank\Standards\Currency\CurrencyAlpha3;

readonly class RateDto
{
    public function __construct(
        private CurrencyAlpha3 $currency,
        private string $rate
    ) {
    }

    public function getCurrency(): CurrencyAlpha3
    {
        return $this->currency;
    }

    public function getRate(): string
    {
        return $this->rate;
    }
}