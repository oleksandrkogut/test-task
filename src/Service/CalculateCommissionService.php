<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RateDto;
use App\Dto\TransactionDto;
use App\Exception\CalculateCommissionException;
use App\Service\Binlist\BinServiceInterface;
use App\Service\EuCountryChecker\EuCountryCheckerInterface;
use App\Service\ExchangeRates\RateServiceInterface;
use DivisionByZeroError;

readonly class CalculateCommissionService
{
    /**
     * @param BCMatchSettingStorageInterface $bcMatchSettingStorage
     * @param CommissionsStorageInterface $commissionsStorage
     * @param BinServiceInterface $binService
     * @param RateServiceInterface $rateService
     * @param EuCountryCheckerInterface $europeanUnionCountryChecker
     */
    public function __construct(
        private BCMatchSettingStorageInterface $bcMatchSettingStorage,
        private CommissionsStorageInterface $commissionsStorage,
        private BinServiceInterface $binService,
        private RateServiceInterface $rateService,
        private EuCountryCheckerInterface $europeanUnionCountryChecker
    ) {
    }

    /**
     * @param TransactionDto $transactionDto
     * @return string
     * @throws CalculateCommissionException
     */
    public function calculate(TransactionDto $transactionDto): string
    {
        $bin = $this->binService->getBinDetails($transactionDto->getBin());

        $rate = $this->rateService->getRateByCurrency($transactionDto->getCurrency());

        $baseTransactionAmount = $this->calculateBaseTransactionAmount($transactionDto, $rate);

        $isEu = $this->europeanUnionCountryChecker->isEUCountry($bin->getCountry());

        return $this->calculateTransactionCommission($isEu, $baseTransactionAmount);
    }

    /**
     * @param TransactionDto $transactionDto
     * @param RateDto $rateDto
     * @return string
     * @throws CalculateCommissionException
     */
    private function calculateBaseTransactionAmount(TransactionDto $transactionDto, RateDto $rateDto): string
    {
        try {
            return bcdiv(
                $transactionDto->getAmount(),
                $rateDto->getRate(),
                $this->bcMatchSettingStorage->getBcMatchScale()
            );
        } catch (DivisionByZeroError $e) {
            throw new CalculateCommissionException(sprintf(
                'Rate for currency %s is 0',
                $rateDto->getCurrency()->value
            ), previous: $e);
        }
    }

    /**
     * @param bool $isEuCountry
     * @param string $baseTransactionAmount
     * @return string
     */
    private function calculateTransactionCommission(
        bool $isEuCountry,
        string $baseTransactionAmount
    ): string {

        $bcMathScale = $this->bcMatchSettingStorage->getBcMatchScale();

        return $isEuCountry
            ? bcmul($baseTransactionAmount, $this->commissionsStorage->getEuCommissions(), $bcMathScale)
            : bcmul($baseTransactionAmount, $this->commissionsStorage->getNormalCommissions(), $bcMathScale);
    }
}