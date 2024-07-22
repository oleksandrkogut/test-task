<?php

namespace App\Tests\Integration;

use App\Dto\TransactionDto;
use App\Exception\CalculateCommissionException;
use App\Service\CalculateCommissionService;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculateCommissionTest extends KernelTestCase
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public static function transactionsDataProvider(): array
    {
        return [
            ['1.00', new TransactionDto('45717360', '100.00', CurrencyAlpha3::Euro)],
            ['0.46', new TransactionDto('516793', '50.00', CurrencyAlpha3::US_Dollar)],
            ['1.18', new TransactionDto('45417360', '10000.00', CurrencyAlpha3::Yen)],
            ['23.66', new TransactionDto('4745030', '2000.00', CurrencyAlpha3::Pound_Sterling)]
        ];
    }

    /**
     * @dataProvider transactionsDataProvider
     * @param string $expectedValue
     * @param TransactionDto $transactionDto
     * @return void
     * @throws CalculateCommissionException
     */
    public function testCalculateCommission(string $expectedValue, TransactionDto $transactionDto): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var CalculateCommissionService $calculateCommissionService */
        $calculateCommissionService = $container->get(CalculateCommissionService::class);

        $result = $calculateCommissionService->calculate($transactionDto);

        $this->assertEquals($expectedValue, $result);
    }
}
