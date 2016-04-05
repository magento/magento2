<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model\Currency\Import;

class YahooFinanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\YahooFinance
     */
    private $model;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyFactoryMock;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientFactoryMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->currencyFactoryMock = $this->getMockBuilder('Magento\Directory\Model\CurrencyFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->httpClientFactoryMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClientFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $scopeMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            'Magento\Directory\Model\Currency\Import\YahooFinance',
            [
                'currencyFactory' => $this->currencyFactoryMock,
                'scopeConfig' => $scopeMock,
                'httpClientFactory' => $this->httpClientFactoryMock
            ]
        );
    }

    public function testFetchRates()
    {
        $currencyFromList = ['USD'];
        $currencyToList = ['EUR', 'UAH'];
        $responseBody = '{"query":{"count":7,"created":"2016-04-05T16:46:55Z","lang":"en-US","results":{"rate":'
            . '[{"id":"USDEUR","Name":"USD/EUR","Rate":"0.9022","Date":"4/5/2016"}]}}}';
        $expectedCurrencyRateList = ['USD' => ['EUR' => 0.9022, 'UAH' => null]];
        $message = "We can't retrieve a rate from http://query.yahooapis.com/v1/public/yql?format=json"
            . "&q=select+*+from+yahoo.finance.xchange+where+pair+in+%28%22USDEUR%22%2C%22USDUAH%22)"
            . "&env=store://datatables.org/alltableswithkeys for UAH.";

        /** @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject $currencyMock */
        $currencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Magento\Framework\HTTP\ZendClient|\PHPUnit_Framework_MockObject_MockObject $currencyMock */
        $httpClientMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClient')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Zend_Http_Response|\PHPUnit_Framework_MockObject_MockObject $currencyMock */
        $httpResponseMock = $this->getMockBuilder('Zend_Http_Response')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->currencyFactoryMock->expects($this->any())->method('create')->willReturn($currencyMock);
        $currencyMock->expects($this->once())->method('getConfigBaseCurrencies')->willReturn($currencyFromList);
        $currencyMock->expects($this->once())->method('getConfigAllowCurrencies')->willReturn($currencyToList);
        $this->httpClientFactoryMock->expects($this->any())->method('create')->willReturn($httpClientMock);
        $httpClientMock->expects($this->atLeastOnce())->method('setUri')->willReturnSelf();
        $httpClientMock->expects($this->atLeastOnce())->method('setConfig')->willReturnSelf();
        $httpClientMock->expects($this->atLeastOnce())->method('request')->willReturn($httpResponseMock);
        $httpResponseMock->expects($this->any())->method('getBody')->willReturn($responseBody);

        $this->assertEquals($expectedCurrencyRateList, $this->model->fetchRates());
        $messages = $this->model->getMessages();
        $this->assertNotEmpty($messages);
        $this->assertTrue(is_array($messages));
        $this->assertEquals($message, (string)$messages[0]);
    }
}
