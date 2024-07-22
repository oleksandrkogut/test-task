<?php

declare(strict_types=1);

namespace App\Service\EuCountryChecker;

use App\Exception\Service\EuCountryChecker\EuCountryCheckerException;
use PrinsFrank\Standards\Country\CountryAlpha2;

readonly class EuCountryChecker implements EuCountryCheckerInterface
{
    /**
     * @param EuCountriesStorageInterface $euCountriesStorage
     */
    public function __construct(
        private EuCountriesStorageInterface $euCountriesStorage
    ) {
    }

    /**
     * @param CountryAlpha2 $country
     * @return bool
     * @throws EuCountryCheckerException
     */
    public function isEUCountry(CountryAlpha2 $country): bool
    {
        return in_array($country, $this->getEuropeanUnionCountries());
    }

    /**
     * @return CountryAlpha2[]
     * @throws EuCountryCheckerException
     */
    private function getEuropeanUnionCountries(): array
    {
        return array_map(function (string $countryCode) {
            $country = CountryAlpha2::tryfrom($countryCode);

            if (is_null($country)) {
                throw new EuCountryCheckerException(sprintf(
                   'Country with code %s is not supported by ISO3166_1',
                    $countryCode
                ));
            }

            return $country;
        }, $this->euCountriesStorage->getEUCountries());
    }
}