<?php

namespace App\Tests\Unit\Service\Binlist;

use App\Dto\BinDto;
use App\Exception\Service\Binlist\BinlistException;
use App\Service\Binlist\BinlistService;
use App\Service\Binlist\BinlistSettingsStorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Standards\Country\CountryAlpha2;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\SerializerInterface;

class BinlistServiceTest extends TestCase
{
    private const string TEST_HOST = 'https://api.binlist.com';
    private const string TEST_BIN_RESPONSE = '
    {
        "number": {},
        "scheme": "visa",
        "type": "debit",
        "brand": "Visa Classic",
        "country": {
            "numeric": "208",
            "alpha2": "DK",
            "name": "Denmark",
            "emoji": "🇩🇰",
            "currency": "DKK",
            "latitude": 56,
            "longitude": 10
        },
        "bank": {
            "name": "Jyske Bank A/S"
        }
    }';

    /** @var BinlistSettingsStorageInterface&MockObject */
    private BinlistSettingsStorageInterface&MockObject $settingsStorage;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface&MockObject $serializer;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->settingsStorage = $this->createMock(BinlistSettingsStorageInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @return void
     * @throws BinlistException
     */
    public function testGetBinDetailsBinNotFound(): void
    {
        $bin = '12345';

        $this->settingsStorage->expects($this->once())
            ->method('getBinlistHost')
            ->willReturn(self::TEST_HOST);

        $mockResponse = new MockResponse(info: [
            'http_code' => 404
        ]);

        $httpClient = new MockHttpClient($mockResponse, 'https://api.binlist.com/12345');

        $this->expectException(BinlistException::class);
        $this->expectExceptionMessage('Bin 12345 not found');

        (new BinlistService($this->settingsStorage, $httpClient, $this->serializer))->getBinDetails($bin);
    }

    /**
     * @return void
     * @throws BinlistException
     */
    public function testGetBinDetailsUnexpectedApiBehaviour(): void
    {
        $bin = '12345';

        $this->settingsStorage->expects($this->once())
            ->method('getBinlistHost')
            ->willReturn(self::TEST_HOST);

        $mockResponse = new MockResponse(info: [
            'http_code' => 429
        ]);

        $httpClient = new MockHttpClient($mockResponse, 'https://api.binlist.com/12345');

        $this->expectException(BinlistException::class);
        $this->expectExceptionMessage('Error during getting bin details 12345');

        (new BinlistService($this->settingsStorage, $httpClient, $this->serializer))->getBinDetails($bin);
    }

    /**
     * @return void
     * @throws BinlistException
     */
    public function testGetBinDetailsSuccess(): void
    {
        $bin = '12345';

        $this->settingsStorage->expects($this->once())
            ->method('getBinlistHost')
            ->willReturn(self::TEST_HOST);

        $mockResponse = new MockResponse(self::TEST_BIN_RESPONSE, [
            'http_code' => 200,
            'response_headers' => ['Content-Type: application/json']
        ]);

        $httpClient = new MockHttpClient($mockResponse, 'https://api.binlist.com/12345');

        $binDto = new BinDto(
            'visa',
            'debit',
            'Visa Classic',
            'Jyske Bank A/S',
            CountryAlpha2::Denmark,
            CurrencyAlpha3::Danish_Krone
        );

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(self::TEST_BIN_RESPONSE, BinDto::class, 'json')
            ->willReturn($binDto);

        $result = (new BinlistService($this->settingsStorage, $httpClient, $this->serializer))->getBinDetails($bin);

        $this->assertEquals($binDto, $result);
    }
}
