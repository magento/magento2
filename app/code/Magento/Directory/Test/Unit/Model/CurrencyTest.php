<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Currency;

class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Currency
     */
    protected $currency;

    protected $currencyCode = 'USD';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeCurrencyMock;

    protected function setUp()
    {
        $this->localeCurrencyMock = $this->getMock('\Magento\Framework\Locale\CurrencyInterface');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->currency = $objectManager->getObject('Magento\Directory\Model\Currency', [
            'localeCurrency' => $this->localeCurrencyMock,
            'data' => [
                'currency_code' => $this->currencyCode,
            ]
        ]);
    }

    public function testGetCurrencySymbol()
    {
        $currencySymbol = '$';

        $currencyMock = $this->getMockBuilder('\Magento\Framework\Currency')
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($this->once())
            ->method('getSymbol')
            ->willReturn($currencySymbol);

        $this->localeCurrencyMock->expects($this->once())
            ->method('getCurrency')
            ->with($this->currencyCode)
            ->willReturn($currencyMock);
        $this->assertEquals($currencySymbol, $this->currency->getCurrencySymbol());
    }

    /**
     * @dataProvider getOutputFormatDataProvider
     * @param $withCurrency
     * @param $noCurrency
     * @param $expected
     */
    public function testGetOutputFormat($withCurrency, $noCurrency, $expected)
    {
        $currencyMock = $this->getMockBuilder('\Magento\Framework\Currency')
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($this->at(0))
            ->method('toCurrency')
            ->willReturn($withCurrency);
        $currencyMock->expects($this->at(1))
            ->method('toCurrency')
            ->willReturn($noCurrency);
        $this->localeCurrencyMock->expects($this->atLeastOnce())
            ->method('getCurrency')
            ->with($this->currencyCode)
            ->willReturn($currencyMock);
        $this->assertEquals($expected, $this->currency->getOutputFormat());
    }

    /**
     * Return data sets for testGetCurrencySymbol()
     *
     * @return array
     */
    public function getOutputFormatDataProvider()
    {
        return [
            'no_unicode' => [
                'withCurrency' => '$0.00',
                'noCurrency' => '0.00',
                'expected' => '$%s',
            ],
            'arabic_unicode' => [
                'withCurrency' => json_decode('"\u200E"') . '$0.00',
                'noCurrency' => json_decode('"\u200E"') . '0.00',
                'expected' => json_decode('"\u200E"') . '$%s',
            ]
        ];
    }
}
