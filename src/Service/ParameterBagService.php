<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ConfigParameterIsNotCorrectlySetException;
use App\Service\Binlist\BinlistSettingsStorageInterface;
use App\Service\EuCountryChecker\EuCountriesStorageInterface;
use App\Service\ExchangeRates\ExchangeRatesSettingsStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class ParameterBagService implements CommissionsStorageInterface,
                                              BinlistSettingsStorageInterface,
                                              ExchangeRatesSettingsStorageInterface,
                                              EuCountriesStorageInterface,
                                              BCMatchSettingStorageInterface
{
    private const string COMMISSION_EU_PARAM_NAME = 'app.commission.eu';
    private const string COMMISSION_NORMAL_PARAM_NAME = 'app.commission.normal';

    private const string BINLIST_HOST_PARAM_NAME = 'app.binlist.host';

    private const string EXCHANGE_RATES_HOST_PARAMETER_NAME = 'app.exchange_rates.host';
    private const string EXCHANGE_RATES_KEY_PARAMETER_NAME = 'app.exchange_rates.key';

    private const string EU_COUNTRIES_PARAM_NAME = 'app.countries.eu';

    private const string BCMATCH_SCALE_PARAM_NAME = 'app.bcmath.scale';

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * @return string
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getEuCommissions(): string
    {
        return $this->getStringValue(self::COMMISSION_EU_PARAM_NAME);
    }

    /**
     * @return string
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getNormalCommissions(): string
    {
        return $this->getStringValue(self::COMMISSION_NORMAL_PARAM_NAME);
    }

    /**
     * @return string
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getBinlistHost(): string
    {
        return $this->getStringValue(self::BINLIST_HOST_PARAM_NAME);
    }

    /**
     * @return string
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getExchangeRatesHost(): string
    {
        return $this->getStringValue(self::EXCHANGE_RATES_HOST_PARAMETER_NAME);
    }

    /**
     * @return string
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getExchangeRatesKey(): string
    {
        return $this->getStringValue(self::EXCHANGE_RATES_KEY_PARAMETER_NAME);
    }

    /**
     * @return string[]
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getEUCountries(): array
    {
        return $this->getStringArrayValue(self::EU_COUNTRIES_PARAM_NAME);
    }

    /**
     * @return int
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function getBcMatchScale(): int
    {
        $value = $this->parameterBag->get(self::BCMATCH_SCALE_PARAM_NAME);
        if (! is_int($value) || $value <= 0) {
            throw new ConfigParameterIsNotCorrectlySetException(self::BCMATCH_SCALE_PARAM_NAME);
        }

        return $value;
    }

    /**
     * @param string $parameterName
     * @return string
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    private function getStringValue(string $parameterName): string
    {
        $value = $this->parameterBag->get($parameterName);
        if (! is_string($value) || empty($value)) {
            throw new ConfigParameterIsNotCorrectlySetException($parameterName);
        }

        return $value;
    }

    /**
     * @param string $parameterName
     * @return string[]
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    private function getStringArrayValue(string $parameterName): array
    {
        $arrayValue = $this->parameterBag->get($parameterName);
        if (! is_array($arrayValue)) {
            throw new ConfigParameterIsNotCorrectlySetException($parameterName);
        }

        array_map(function (mixed $value) use ($parameterName): void {
            if (! is_string($value) || empty($value)) {
                throw new ConfigParameterIsNotCorrectlySetException($parameterName);
            }
        }, $arrayValue);

        return $arrayValue;
    }
}