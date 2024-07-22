<?php

declare(strict_types=1);

namespace App\Service\EuCountryChecker;

use PrinsFrank\Standards\Country\CountryAlpha2;

interface EuCountryCheckerInterface
{
    /**
     * @param CountryAlpha2 $country
     * @return bool
     */
    public function isEUCountry(CountryAlpha2 $country): bool;
}