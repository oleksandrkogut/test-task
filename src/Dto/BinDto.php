<?php

declare(strict_types=1);

namespace App\Dto;

use PrinsFrank\Standards\Country\CountryAlpha2;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Symfony\Component\Serializer\Attribute\SerializedPath;

readonly class BinDto
{
    /**
     * @param string $scheme
     * @param string $type
     * @param string $brand
     * @param string $bank
     * @param CountryAlpha2 $country
     * @param CurrencyAlpha3 $currency
     */
    public function __construct(
        private string $scheme,
        private string $type,
        private string $brand,
        #[SerializedPath('[bank][name]')]
        private string $bank,
        #[SerializedPath('[country][alpha2]')]
        private CountryAlpha2 $country,
        #[SerializedPath('[country][currency]')]
        private CurrencyAlpha3 $currency
    ) {
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getBank(): string
    {
        return $this->bank;
    }

    /**
     * @return CountryAlpha2
     */
    public function getCountry(): CountryAlpha2
    {
        return $this->country;
    }

    /**
     * @return CurrencyAlpha3
     */
    public function getCurrency(): CurrencyAlpha3
    {
        return $this->currency;
    }
}