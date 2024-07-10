<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Currency\Import;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\Currency\Import\FixerIoApiLayer;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FixerIoApiLayerTest extends TestCase
{
    /**
     * @var FixerIoApiLayer
     */
    private $model;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $currencyFactory;

    /**
     * @var LaminasClientFactory|MockObject
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
        $this->httpClientFactory = $this->getMockBuilder(LaminasClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->model = new FixerIoApiLayer($this->currencyFactory, $this->scopeConfig, $this->httpClientFactory);
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
        $responseBody = '{"success":"true","base":"USD","date":"2015-10-07","rates":{"EUR":0.9022}}';
        $expectedCurrencyRateList = ['USD' => ['EUR' => 0.9022, 'UAH' => null]];
        $message = "We can't retrieve a rate from "
            . "https://api.apilayer.com for UAH.";

        $this->scopeConfig->method('getValue')
            ->withConsecutive(
                ['currency/fixerio_apilayer/api_key', 'store'],
                ['currency/fixerio_apilayer/timeout', 'store']
            )
            ->willReturnOnConsecutiveCalls('api_key', 100);

        /** @var Currency|MockObject $currency */
        $currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var LaminasClient|MockObject $httpClient */
        $httpClient = $this->getMockBuilder(LaminasClient::class)
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
        $httpClient->method('setOptions')
            ->willReturnSelf();
        $httpClient->method('setMethod')
            ->willReturnSelf();
        $httpClient->method('send')
            ->willReturn($httpResponse);
        $httpResponse->method('getBody')
            ->willReturn($responseBody);

        self::assertEquals($expectedCurrencyRateList, $this->model->fetchRates());

        $messages = $this->model->getMessages();
        self::assertNotEmpty($messages);
        self::assertIsArray($messages);
        self::assertEquals($message, (string)$messages[0]);
    }
}
