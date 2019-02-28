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
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * CurrencyConverterApiTest Test
 */
class CurrencyConverterApiTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp()
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
            ->getMock();

        $this->model = new CurrencyConverterApi($this->currencyFactory, $this->scopeConfig, $this->httpClientFactory);
    }

    /**
     * Test Fetch Rates
     *
     * @return void
     */
    public function testFetchRates(): void
    {
        $currencyFromList = ['USD'];
        $currencyToList = ['EUR', 'UAH'];
        $responseBody = '{"USD_EUR":0.879215}';
        $expectedCurrencyRateList = ['USD' => ['EUR' => 0.879215, 'UAH' => null]];
        $message = "We can't retrieve a rate for UAH.";

        $this->scopeConfig->method('getValue')
            ->withConsecutive(
                ['currency/currencyconverterapi/api_key', 'store'],
                ['currency/currencyconverterapi/timeout', 'store']
            )
            ->willReturnOnConsecutiveCalls('api_key', 100);

        /** @var Currency|MockObject $currency */
        $currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ZendClient|MockObject $httpClient */
        $httpClient = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var DataObject|MockObject $currencyMock */
        $httpResponse = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();

        $this->currencyFactory->method('create')
            ->willReturn($currency);
        $currency->method('getConfigBaseCurrencies')
            ->willReturn($currencyFromList);
        $currency->method('getConfigAllowCurrencies')
            ->willReturn($currencyToList);

        $this->httpClientFactory->method('create')
            ->willReturn($httpClient);
        $httpClient->method('setUri')
            ->willReturnSelf();
        $httpClient->method('setConfig')
            ->willReturnSelf();
        $httpClient->method('request')
            ->willReturn($httpResponse);
        $httpResponse->method('getBody')
            ->willReturn($responseBody);

        self::assertEquals($expectedCurrencyRateList, $this->model->fetchRates());

        $messages = $this->model->getMessages();
        self::assertNotEmpty($messages);
        self::assertTrue(is_array($messages));
        self::assertEquals($message, (string)$messages[0]);
    }
}
