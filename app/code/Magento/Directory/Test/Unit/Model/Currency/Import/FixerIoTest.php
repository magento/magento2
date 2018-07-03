<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model\Currency\Import;

class FixerIoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\FixerIo
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

        $this->currencyFactoryMock = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->httpClientFactoryMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $scopeMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            \Magento\Directory\Model\Currency\Import\FixerIo::class,
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
        $responseBody = '{"base":"USD","date":"2015-10-07","rates":{"EUR":0.9022}}';
        $expectedCurrencyRateList = ['USD' => ['EUR' => 0.9022, 'UAH' => null]];
        $message = "We can't retrieve a rate from http://api.fixer.io/latest?base=USD&symbols=EUR,UAH for UAH.";

        /** @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject $currencyMock */
        $currencyMock = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Magento\Framework\HTTP\ZendClient|\PHPUnit_Framework_MockObject_MockObject $currencyMock */
        $httpClientMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Zend_Http_Response|\PHPUnit_Framework_MockObject_MockObject $currencyMock */
        $httpResponseMock = $this->getMockBuilder(\Zend_Http_Response::class)
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
