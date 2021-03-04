<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Currency\Import;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\Currency\Import\CurrencyConverterApi;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * CurrencyConverterApi converter test.
 */
class CurrencyConverterApiTest extends TestCase
{
    /**
     * @var CurrencyConverterApi
     */
    private $model;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $currencyFactory;

    /**
     * @var ZendClientFactory|MockObject
     */
    private $httpClientFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            CurrencyConverterApi::class,
            [
                'currencyFactory' => $this->currencyFactory,
                'scopeConfig' => $this->scopeConfig,
                'httpClientFactory' => $this->httpClientFactory,
            ]
        );
    }

    /**
     * Prepare CurrencyFactory mock.
     */
    private function prepareCurrencyFactoryMock(): void
    {
        $currencyFromList = ['USD'];
        $currencyToList = ['EUR', 'UAH'];

        /** @var Currency|MockObject $currency */
        $currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();
        $currency->expects($this->once())->method('getConfigBaseCurrencies')->willReturn($currencyFromList);
        $currency->expects($this->once())->method('getConfigAllowCurrencies')->willReturn($currencyToList);

        $this->currencyFactory->expects($this->atLeastOnce())->method('create')->willReturn($currency);
    }

    /**
     * Prepare FetchRates test.
     *
     * @param string $responseBody
     */
    private function prepareFetchRatesTest(string $responseBody): void
    {
        $this->prepareCurrencyFactoryMock();

        $this->scopeConfig->method('getValue')
            ->withConsecutive(
                ['currency/currencyconverterapi/api_key', 'store'],
                ['currency/currencyconverterapi/timeout', 'store']
            )
            ->willReturnOnConsecutiveCalls('api_key', 100);

        /** @var ZendClient|MockObject $httpClient */
        $httpClient = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var DataObject|MockObject $currencyMock */
        $httpResponse = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();

        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($httpClient);
        $httpClient->expects($this->once())->method('setUri')->willReturnSelf();
        $httpClient->expects($this->once())->method('setConfig')->willReturnSelf();
        $httpClient->expects($this->once())->method('request')->willReturn($httpResponse);
        $httpResponse->expects($this->once())->method('getBody')->willReturn($responseBody);
    }

    /**
     * Test Fetch Rates
     *
     * @return void
     */
    public function testFetchRates(): void
    {
        $expectedCurrencyRateList = ['USD' => ['EUR' => 0.891285, 'UAH' => 26.16]];
        $responseBody = '{"USD_EUR":0.891285,"USD_UAH":26.16,"USD_USD":1}';
        $this->prepareFetchRatesTest($responseBody);

        self::assertEquals($expectedCurrencyRateList, $this->model->fetchRates());
    }

    /**
     * Test FetchRates when Service Response is empty.
     */
    public function testFetchRatesWhenServiceResponseIsEmpty(): void
    {
        $responseBody = '';
        $expectedCurrencyRateList = ['USD' => ['EUR' => null, 'UAH' => null]];
        $cantRetrieveCurrencyMessage = "We can't retrieve a rate from "
            . "https://free.currconv.com for %s.";
        $this->prepareFetchRatesTest($responseBody);

        self::assertEquals($expectedCurrencyRateList, $this->model->fetchRates());

        $messages = $this->model->getMessages();
        self::assertEquals(sprintf($cantRetrieveCurrencyMessage, 'EUR'), (string) $messages[0]);
        self::assertEquals(sprintf($cantRetrieveCurrencyMessage, 'UAH'), (string) $messages[1]);
    }

    /**
     * Test FetchRates when Service Response has error.
     */
    public function testFetchRatesWhenServiceResponseHasError(): void
    {
        $serviceErrorMessage = 'Service error';
        $responseBody = sprintf('{"error":"%s"}', $serviceErrorMessage);
        $expectedCurrencyRateList = ['USD' => ['EUR' => null, 'UAH' => null]];
        $this->prepareFetchRatesTest($responseBody);

        self::assertEquals($expectedCurrencyRateList, $this->model->fetchRates());

        $messages = $this->model->getMessages();
        self::assertEquals($serviceErrorMessage, (string) $messages[0]);
    }

    /**
     * Test FetchRates when Service URL is empty.
     */
    public function testFetchRatesWhenServiceUrlIsEmpty(): void
    {
        $this->prepareCurrencyFactoryMock();

        $this->scopeConfig->method('getValue')
            ->withConsecutive(
                ['currency/currencyconverterapi/api_key', 'store'],
                ['currency/currencyconverterapi/timeout', 'store']
            )
            ->willReturnOnConsecutiveCalls('', 100);

        $expectedCurrencyRateList = ['USD' => ['EUR' => null, 'UAH' => null]];
        self::assertEquals($expectedCurrencyRateList, $this->model->fetchRates());

        $noApiKeyErrorMessage = 'No API Key was specified or an invalid API Key was specified.';
        $messages = $this->model->getMessages();
        self::assertEquals($noApiKeyErrorMessage, (string) $messages[0]);
    }
}
