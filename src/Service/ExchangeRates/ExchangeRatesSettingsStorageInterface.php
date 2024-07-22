<?php

declare(strict_types=1);

namespace App\Service\ExchangeRates;

interface ExchangeRatesSettingsStorageInterface
{
    /**
     * @return string
     */
    public function getExchangeRatesHost(): string;

    /**
     * @return string
     */
    public function getExchangeRatesKey(): string;
}