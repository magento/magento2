<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    public function setUp()
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
}
