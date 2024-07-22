<?php

namespace App\Tests\Unit\Service;

use App\Exception\ConfigParameterIsNotCorrectlySetException;
use App\Service\ParameterBagService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ParameterBagServiceTest extends TestCase
{
    /** @var ParameterBagInterface&MockObject */
    private ParameterBagInterface&MockObject $parameterBag;

    /** @var ParameterBagService */
    private ParameterBagService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);

        $this->service = new ParameterBagService($this->parameterBag);
    }

    /**
     * @return array<int, string[]>
     */
    public static function parameterWithStringValueProvider(): array
    {
        return [
            ['app.commission.eu', 'getEuCommissions'],
            ['app.commission.normal', 'getNormalCommissions'],
            ['app.binlist.host', 'getBinlistHost'],
            ['app.exchange_rates.host', 'getExchangeRatesHost'],
            ['app.exchange_rates.key', 'getExchangeRatesKey'],
        ];
    }

    /**
     * @dataProvider parameterWithStringValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetStringParameterIsNotString(string $parameterName, string $getterName): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn(null);

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage(sprintf('Config parameter %s is not correctly set', $parameterName));

        $this->service->$getterName();
    }

    /**
     * @dataProvider parameterWithStringValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetStringParameterIsEmpty(string $parameterName, string $getterName): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn('');

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage(sprintf('Config parameter %s is not correctly set', $parameterName));

        $this->service->$getterName();
    }

    /**
     * @dataProvider parameterWithStringValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetStringParameterSuccess(string $parameterName, string $getterName): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn('');

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage(sprintf('Config parameter %s is not correctly set', $parameterName));

        $this->service->$getterName();
    }

    /**
     * @return array<int, string[]>
     */
    public static function parameterWithArrayValueProvider(): array
    {
        return [
            ['app.countries.eu', 'getEUCountries'],
        ];
    }

    /**
     * @dataProvider parameterWithArrayValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetArrayParameterIsNull(string $parameterName, string $getterName): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn(null);

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage(sprintf('Config parameter %s is not correctly set', $parameterName));

        $this->service->$getterName();
    }

    /**
     * @dataProvider parameterWithArrayValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetArrayParameterIsIntArray(string $parameterName, string $getterName): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn([1,2,3]);

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage(sprintf('Config parameter %s is not correctly set', $parameterName));

        $this->service->$getterName();
    }

    /**
     * @dataProvider parameterWithArrayValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetArrayParameterArrayHasEmptyString(string $parameterName, string $getterName): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn(['value1', '', 'value2']);

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage(sprintf('Config parameter %s is not correctly set', $parameterName));

        $this->service->$getterName();
    }

    /**
     * @dataProvider parameterWithArrayValueProvider
     * @param string $parameterName
     * @param string $getterName
     * @return void
     */
    public function testGetArrayParameterSuccess(string $parameterName, string $getterName): void
    {
        $array = ['value1', 'value2', 'value3'];

        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with($parameterName)
            ->willReturn($array);

        $result = $this->service->$getterName();

        $this->assertSame($array, $result);
    }

    /**
     * @return void
     */
    public function testGetBcMatchScaleValueIsString(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('app.bcmath.scale')
            ->willReturn('test');

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage('Config parameter app.bcmath.scale is not correctly set');

        $this->service->getBcMatchScale();
    }

    /**
     * @return void
     */
    public function testGetBcMatchScaleValueIsLessThanZero(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('app.bcmath.scale')
            ->willReturn(-1);

        $this->expectException(ConfigParameterIsNotCorrectlySetException::class);
        $this->expectExceptionMessage('Config parameter app.bcmath.scale is not correctly set');

        $this->service->getBcMatchScale();
    }

    /**
     * @return void
     * @throws ConfigParameterIsNotCorrectlySetException
     */
    public function testGetBcMatchScaleSuccess(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('app.bcmath.scale')
            ->willReturn(2);

        $result = $this->service->getBcMatchScale();

        $this->assertSame(2, $result);
    }
}
