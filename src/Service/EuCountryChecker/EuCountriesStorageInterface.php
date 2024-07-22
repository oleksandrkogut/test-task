<?php

declare(strict_types=1);

namespace App\Service\EuCountryChecker;

interface EuCountriesStorageInterface
{
    /**
     * @return string[]
     */
    public function getEUCountries(): array;
}