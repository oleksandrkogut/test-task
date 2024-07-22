<?php

declare(strict_types=1);

namespace App\Service\ExchangeRates\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class RatesResponseDto
{
    /**
     * @param int $timestamp
     * @param string $baseCurrency
     * @param array<string, int|float> $rates
     */
    public function __construct(
        private int $timestamp,
        #[SerializedName('base')]
        private string $baseCurrency,
        private array $rates
    ) {
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    /**
     * @return array<string, int|float>
     */
    public function getRates(): array
    {
        return $this->rates;
    }
}