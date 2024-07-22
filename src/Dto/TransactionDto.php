<?php

declare(strict_types=1);

namespace App\Dto;

use PrinsFrank\Standards\Currency\CurrencyAlpha3;

readonly class TransactionDto
{
    /**
     * @param string|null $bin
     * @param string|null $amount
     * @param CurrencyAlpha3|null $currency
     */
    public function __construct(
        private ?string $bin,
        private ?string $amount,
        private ?CurrencyAlpha3 $currency
    ) {
    }

    /**
     * @return string
     */
    public function getBin(): string
    {
        /** @var string $bin */
        $bin = $this->bin;

        return $bin;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        /** @var string $amount */
        $amount = $this->amount;

        return $amount;
    }

    /**
     * @return CurrencyAlpha3
     */
    public function getCurrency(): CurrencyAlpha3
    {
        /** @var CurrencyAlpha3 $currencyEnum */
        $currencyEnum = $this->currency;

        return $currencyEnum;
    }
}