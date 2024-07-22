<?php

namespace App\Tests\Unit\Service\EuCountryChecker;

use App\Exception\Service\EuCountryChecker\EuCountryCheckerException;
use App\Service\EuCountryChecker\EuCountriesStorageInterface;
use App\Service\EuCountryChecker\EuCountryChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Standards\Country\CountryAlpha2;

class EuCountryCheckerTest extends TestCase
{
    /** @var EuCountriesStorageInterface&MockObject */
    private EuCountriesStorageInterface&MockObject $euCountriesStorage;

    /** @var EuCountryChecker */
    private EuCountryChecker $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->euCountriesStorage = $this->createMock(EuCountriesStorageInterface::class);

        $this->service = new EuCountryChecker($this->euCountriesStorage);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function getCountryCodes(): array
    {
        return [
            'UA' => [CountryAlpha2::from('UA'), false],
            'PL' => [CountryAlpha2::from('PL'), true]
        ];
    }

    /**
     * @dataProvider getCountryCodes
     * @param CountryAlpha2 $country
     * @param bool $expected
     * @return void
     * @throws EuCountryCheckerException
     */
    public function testIsEuropeanCountrySuccess(CountryAlpha2 $country, bool $expected): void
    {
        $this->euCountriesStorage->expects($this->once())
            ->method('getEUCountries')
            ->willReturn(['LT', 'PL']);

        $this->assertSame($this->service->isEUCountry($country), $expected);
    }

    /**
     * @return void
     * @throws EuCountryCheckerException
     */
    public function testIsEuropeanCountryConfigParamCountryCodeNotSupported(): void
    {
        $this->euCountriesStorage->expects($this->once())
            ->method('getEUCountries')
            ->willReturn(['Atlantis']);

        $this->expectException(EuCountryCheckerException::class);
        $this->expectExceptionMessage('Country with code Atlantis is not supported by ISO3166_1');

        $this->service->isEUCountry(CountryAlpha2::Denmark);
    }
}
