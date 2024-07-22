<?php

namespace App\Tests\Unit\Service\ExchangeRates;

use App\Dto\RateDto;
use App\Exception\Service\ExchangeRates\ExchangeRatesException;
use App\Service\ExchangeRates\Dto\RatesResponseDto;
use App\Service\ExchangeRates\ExchangeRatesService;
use App\Service\ExchangeRates\ExchangeRatesSettingsStorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ExchangeRatesServiceTest extends TestCase
{
    private const string TEST_HOST = 'https://api.exchange-rates.com';
    private const string TEST_KEY = '12345';
    private const string TEST_RESPONSE = '{"success":true,"timestamp":1718042765,"base":"EUR","date":"2024-06-10","rates":{"AED":3.95171,"AFN":75.599863,"ALL":100.406095,"AMD":416.945005,"ANG":1.93766,"AOA":919.874558,"ARS":970.162335,"AUD":1.628737,"AWG":1.939267,"AZN":1.831107,"BAM":1.958387,"BBD":2.170767,"BDT":126.32687,"BGN":1.956422,"BHD":0.405531,"BIF":3116.746,"BMD":1.075876,"BND":1.455336,"BOB":7.428882,"BRL":5.773474,"BSD":1.07512,"BTC":1.5386818e-5,"BTN":89.791444,"BWP":14.767644,"BYN":3.51788,"BYR":21087.172866,"BZD":2.167083,"CAD":1.480691,"CDF":3042.578205,"CHF":0.964415,"CLF":0.035926,"CLP":991.29074,"CNY":7.797738,"CNH":7.817053,"COP":4266.161005,"CRC":569.251947,"CUC":1.075876,"CUP":28.510718,"CVE":110.410846,"CZK":24.623038,"DJF":191.204972,"DKK":7.458829,"DOP":64.405075,"DZD":145.041743,"EGP":51.321875,"ERN":16.138142,"ETB":61.378385,"EUR":1,"FJD":2.409694,"FKP":0.856504,"GBP":0.845036,"GEL":3.082412,"GGP":0.856504,"GHS":16.019309,"GIP":0.856504,"GMD":72.917528,"GNF":9258.229555,"GTQ":8.354035,"GYD":225.049368,"HKD":8.406165,"HNL":26.676324,"HRK":7.509568,"HTG":142.608377,"HUF":393.139681,"IDR":17543.021598,"ILS":4.030802,"IMP":0.856504,"INR":89.861212,"IQD":1407.359035,"IRR":45294.386307,"ISK":149.740458,"JEP":0.856504,"JMD":167.090717,"JOD":0.762584,"JPY":168.920632,"KES":139.216262,"KGS":93.612415,"KHR":4459.932753,"KMF":486.77998,"KPW":968.288224,"KRW":1479.227513,"KWD":0.329949,"KYD":0.895992,"KZT":482.41172,"LAK":23319.019744,"LBP":96344.710503,"LKR":325.880468,"LRD":208.517377,"LSL":20.164336,"LTL":3.176782,"LVL":0.650786,"LYD":5.197866,"MAD":10.732177,"MDL":19.029313,"MGA":4817.240874,"MKD":61.68358,"MMK":3020.340058,"MNT":3711.772531,"MOP":8.653111,"MRU":42.163696,"MUR":50.137491,"MVR":16.56364,"MWK":1864.079661,"MXN":19.732887,"MYR":5.07922,"MZN":68.323596,"NAD":20.164336,"NGN":1615.826059,"NIO":39.537953,"NOK":11.463783,"NPR":143.666109,"NZD":1.75746,"OMR":0.414169,"PAB":1.07513,"PEN":4.033728,"PGK":4.189731,"PHP":63.20611,"PKR":299.094986,"PLN":4.327125,"PYG":8090.687303,"QAR":3.920178,"RON":4.977329,"RSD":117.093053,"RUB":95.591604,"RWF":1410.576396,"SAR":4.034742,"SBD":9.07977,"SCR":14.766094,"SDG":630.463414,"SEK":11.283886,"SGD":1.455365,"SHP":1.359316,"SLE":24.580869,"SLL":22560.586288,"SOS":614.417319,"SRD":34.045561,"STD":22268.464464,"SVC":9.407427,"SYP":2703.170989,"SZL":20.154811,"THB":39.506119,"TJS":11.466153,"TMT":3.776325,"TND":3.353029,"TOP":2.542405,"TRY":34.860325,"TTD":7.287694,"TWD":34.80728,"TZS":2813.416062,"UAH":43.507066,"UGX":4058.360847,"USD":1.075876,"UYU":41.765169,"UZS":13623.291969,"VEF":3897417.934247,"VES":39.199849,"VND":27351.461847,"VUV":127.730159,"WST":3.015781,"XAF":656.830738,"XAG":0.036198,"XAU":0.000466,"XCD":2.907609,"XDR":0.810478,"XOF":656.284365,"XPF":119.331742,"YER":269.426288,"ZAR":20.157395,"ZMK":9684.179394,"ZMW":28.436828,"ZWL":346.431687}}';

    /** @var MockObject&ExchangeRatesSettingsStorageInterface */
    private ExchangeRatesSettingsStorageInterface&MockObject $settingsStorage;

    /** @var MockObject&SerializerInterface */
    private SerializerInterface&MockObject $serializer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->settingsStorage = $this->createMock(
            ExchangeRatesSettingsStorageInterface::class
        );

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @return void
     * @throws ExchangeRatesException
     */
    public function testGetRateByCurrencyUnexpectedApiBehaviour(): void
    {
        $this->settingsStorage->expects($this->once())
            ->method('getExchangeRatesHost')
            ->willReturn(self::TEST_HOST);

        $this->settingsStorage->expects($this->once())
            ->method('getExchangeRatesKey')
            ->willReturn(self::TEST_KEY);

        $mockResponse = new MockResponse(info: [
            'http_code' => 429
        ]);

        $httpClient = new MockHttpClient(
            $mockResponse,
            'https://api.exchange-rates.com/v1/latest?access_key=12345'
        );

        $this->expectException(ExchangeRatesException::class);
        $this->expectExceptionMessage('Error during getting exchange rates');

        (new ExchangeRatesService($this->settingsStorage, $httpClient, $this->serializer))
            ->getRateByCurrency(CurrencyAlpha3::US_Dollar);
    }

    /**
     * @return void
     * @throws ExchangeRatesException
     */
    public function testGetRateByCurrencyRateNotFound(): void
    {
        $this->settingsStorage->expects($this->once())
            ->method('getExchangeRatesHost')
            ->willReturn(self::TEST_HOST);

        $this->settingsStorage->expects($this->once())
            ->method('getExchangeRatesKey')
            ->willReturn(self::TEST_KEY);

        $mockResponse = new MockResponse(self::TEST_RESPONSE, [
            'http_code' => 200,
            'response_headers' => ['Content-Type: application/json']
        ]);

        $httpClient = new MockHttpClient(
            $mockResponse,
            'https://api.exchange-rates.com/v1/latest?access_key=12345'
        );

        $responseDto = new RatesResponseDto(0,'EUR', []);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(self::TEST_RESPONSE, RatesResponseDto::class, 'json')
            ->willReturn($responseDto);

        $this->expectException(ExchangeRatesException::class);
        $this->expectExceptionMessage('Exchange rate for currency USD not found');

        (new ExchangeRatesService($this->settingsStorage, $httpClient, $this->serializer))
            ->getRateByCurrency(CurrencyAlpha3::US_Dollar);
    }

    /**
     * @return void
     * @throws ExchangeRatesException
     */
    public function testGetRateByCurrencySuccess(): void
    {
        $this->settingsStorage->expects($this->once())
            ->method('getExchangeRatesHost')
            ->willReturn(self::TEST_HOST);

        $this->settingsStorage->expects($this->once())
            ->method('getExchangeRatesKey')
            ->willReturn(self::TEST_KEY);

        $mockResponse = new MockResponse(self::TEST_RESPONSE, [
            'http_code' => 200,
            'response_headers' => ['Content-Type: application/json']
        ]);

        $httpClient = new MockHttpClient(
            $mockResponse,
            'https://api.exchange-rates.com/v1/latest?access_key=12345'
        );

        $responseDto = new RatesResponseDto(0,'EUR', ['USD' => 1.075876]);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(self::TEST_RESPONSE, RatesResponseDto::class, 'json')
            ->willReturn($responseDto);

        $result = (new ExchangeRatesService($this->settingsStorage, $httpClient, $this->serializer))
            ->getRateByCurrency(CurrencyAlpha3::US_Dollar);

        $rate = new RateDto(CurrencyAlpha3::US_Dollar, '1.075876');

        $this->assertEquals($result, $rate);
    }
}
