<?php

namespace App\Tests\Unit\Service;

use App\Dto\BinDto;
use App\Dto\RateDto;
use App\Dto\TransactionDto;
use App\Exception\CalculateCommissionException;
use App\Service\BCMatchSettingStorageInterface;
use App\Service\Binlist\BinServiceInterface;
use App\Service\CalculateCommissionService;
use App\Service\CommissionsStorageInterface;
use App\Service\EuCountryChecker\EuCountryCheckerInterface;
use App\Service\ExchangeRates\RateServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Standards\Country\CountryAlpha2;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class CalculateCommissionServiceTest extends TestCase
{
    private const int TEST_BC_MATH_SCALE = 2;

    /** @var MockObject&BCMatchSettingStorageInterface */
    private BCMatchSettingStorageInterface&MockObject $bCMatchSettingStorage;

    /** @var MockObject&CommissionsStorageInterface */
    private CommissionsStorageInterface&MockObject $commissionsStorage;

    /** @var BinServiceInterface&MockObject */
    private BinServiceInterface&MockObject $binService;

    /** @var RateServiceInterface&MockObject */
    private RateServiceInterface&MockObject $ratesService;

    /** @var EuCountryCheckerInterface&MockObject */
    private EuCountryCheckerInterface&MockObject $euCountryChecker;

    /** @var CalculateCommissionService */
    private CalculateCommissionService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->bCMatchSettingStorage = $this->createMock(BCMatchSettingStorageInterface::class);
        $this->commissionsStorage = $this->createMock(CommissionsStorageInterface::class);
        $this->binService = $this->createMock(BinServiceInterface::class);
        $this->ratesService = $this->createMock(RateServiceInterface::class);
        $this->euCountryChecker = $this->createMock(EuCountryCheckerInterface::class);

        $this->service = new CalculateCommissionService(
            $this->bCMatchSettingStorage,
            $this->commissionsStorage,
            $this->binService,
            $this->ratesService,
            $this->euCountryChecker
        );
    }

    /**
     * @return void
     * @throws CalculateCommissionException
     */
    public function testCalculateRateForCurrencyIsNull(): void
    {
        $transaction = new TransactionDto(
            '12345',
            '100',
            CurrencyAlpha3::Euro
        );

        $bin = new BinDto(
            '',
            '',
            '',
            '',
            CountryAlpha2::Portugal,
            CurrencyAlpha3::Euro
        );

        $this->binService->expects($this->once())
            ->method('getBinDetails')
            ->willReturn($bin);

        $rate = new RateDto(CurrencyAlpha3::Euro, '0');

        $this->ratesService->expects($this->once())
            ->method('getRateByCurrency')
            ->with(CurrencyAlpha3::Euro)
            ->willReturn($rate);

        $this->bCMatchSettingStorage->expects($this->once())
            ->method('getBcMatchScale')
            ->willReturn(2);

        $this->expectException(CalculateCommissionException::class);
        $this->expectExceptionMessage('Rate for currency EUR is 0');

        $this->service->calculate($transaction);
    }

    /**
     * @return void
     * @throws CalculateCommissionException
     */
    public function testCalculateRateSuccessEuCountry(): void
    {
        $transaction = new TransactionDto(
            '12345',
            '120',
            CurrencyAlpha3::Zloty
        );

        $bin = new BinDto(
            '',
            '',
            '',
            '',
            CountryAlpha2::Poland,
            CurrencyAlpha3::Zloty
        );

        $this->binService->expects($this->once())
            ->method('getBinDetails')
            ->with('12345')
            ->willReturn($bin);

        $rate = new RateDto(CurrencyAlpha3::Zloty, '1.2');

        $this->ratesService->expects($this->once())
            ->method('getRateByCurrency')
            ->with(CurrencyAlpha3::Zloty)
            ->willReturn($rate);

        $this->euCountryChecker->expects($this->once())
            ->method('isEUCountry')
            ->willReturn(true);

        $this->bCMatchSettingStorage->expects($this->atLeast(1))
            ->method('getBcMatchScale')
            ->willReturn(self::TEST_BC_MATH_SCALE);

        $this->commissionsStorage->expects($this->once())
            ->method('getEuCommissions')
            ->willReturn('0.01');

        $result = $this->service->calculate($transaction);

        $this->assertSame('1.00', $result);
    }

    /**
     * @return void
     * @throws CalculateCommissionException
     */
    public function testCalculateRateSuccessNotEuCountry(): void
    {
        $transaction = new TransactionDto(
            '12345',
            '150',
            CurrencyAlpha3::Pound_Sterling
        );

        $bin = new BinDto(
            '',
            '',
            '',
            '',
            CountryAlpha2::United_Kingdom,
            CurrencyAlpha3::Pound_Sterling
        );

        $this->binService->expects($this->once())
            ->method('getBinDetails')
            ->with('12345')
            ->willReturn($bin);

        $rate = new RateDto(CurrencyAlpha3::Pound_Sterling, '1.5');

        $this->ratesService->expects($this->once())
            ->method('getRateByCurrency')
            ->with(CurrencyAlpha3::Pound_Sterling)
            ->willReturn($rate);

        $this->euCountryChecker->expects($this->once())
            ->method('isEUCountry')
            ->willReturn(false);

        $this->bCMatchSettingStorage->expects($this->atLeast(1))
            ->method('getBcMatchScale')
            ->willReturn(self::TEST_BC_MATH_SCALE);

        $this->commissionsStorage->expects($this->once())
            ->method('getNormalCommissions')
            ->willReturn('0.02');

        $result = $this->service->calculate($transaction);

        $this->assertSame('2.00', $result);
    }
}
