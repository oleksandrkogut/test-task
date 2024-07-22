<?php

declare(strict_types=1);

namespace App\Service\ExchangeRates;

use App\Dto\RateDto;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

interface RateServiceInterface
{
    /**
     * @param CurrencyAlpha3 $currency
     * @return RateDto
     */
    public function getRateByCurrency(CurrencyAlpha3 $currency): RateDto;
}