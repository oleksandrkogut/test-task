<?php

declare(strict_types=1);

namespace App\Service\ExchangeRates;

use App\Dto\RateDto;
use App\Exception\Service\ExchangeRates\ExchangeRatesException;
use App\Service\ExchangeRates\Dto\RatesResponseDto;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class ExchangeRatesService implements RateServiceInterface
{
    /**
     * @param ExchangeRatesSettingsStorageInterface $settingsStorage
     * @param HttpClientInterface $httpClient
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private ExchangeRatesSettingsStorageInterface $settingsStorage,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @param CurrencyAlpha3 $currency
     * @return RateDto
     * @throws ExchangeRatesException
     */
    public function getRateByCurrency(CurrencyAlpha3 $currency): RateDto
    {
        $filteredRates = array_filter($this->getRates(), function (RateDto $dto) use ($currency) {
            return $dto->getCurrency() === $currency;
        });

        if (empty($filteredRates)) {
            throw new ExchangeRatesException(sprintf(
                'Exchange rate for currency %s not found',
                $currency->value
            ));
        }

        return $filteredRates[array_key_first($filteredRates)];
    }

    /**
     * @return RateDto[]
     * @throws ExchangeRatesException
     */
    private function getRates(): array
    {
        $responseDto = $this->getExchangeRates();

        $rates = [];

        foreach ($responseDto->getRates() as $currency => $rate) {
            $currency = CurrencyAlpha3::tryFrom($currency);

            if (is_null($currency) || $rate === 0) {
                continue;
            }

            $rates[] = new RateDto($currency, (string) $rate);
        }

        return $rates;
    }

    /**
     * @return RatesResponseDto
     * @throws ExchangeRatesException
     */
    private function getExchangeRates(): RatesResponseDto
    {
        try {
            return $this->getExchangeRatesProcess();
        } catch (
            TransportExceptionInterface
            | ClientExceptionInterface
            | RedirectionExceptionInterface
            | ServerExceptionInterface $e
        ) {
            throw new ExchangeRatesException('Error during getting exchange rates', previous: $e);
        }
    }

    /**
     * @return RatesResponseDto
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getExchangeRatesProcess(): RatesResponseDto
    {
        $url = sprintf(
            '%s/v1/latest?access_key=%s',
            $this->settingsStorage->getExchangeRatesHost(),
            $this->settingsStorage->getExchangeRatesKey()
        );

        $response = $this->httpClient->request('GET', $url);

        return $this->serializer->deserialize(
            $response->getContent(),
            RatesResponseDto::class,
            'json'
        );
    }
}