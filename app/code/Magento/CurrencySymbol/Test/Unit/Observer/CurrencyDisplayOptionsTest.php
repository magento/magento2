<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Observer;

use \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory;

/**
 * Test for \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions
 */
class CurrencyDisplayOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions
     */
    private $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CurrencysymbolFactory $mockSymbolFactory
     */
    private $mockSymbolFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CurrencySymbol\Model\System\Currencysymbol $mockSymbol
     */
    private $mockSymbol;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer $mockEvent
     */
    private $mockEventObserver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event $mockEvent
     */
    private $mockEvent;

    protected function setUp()
    {
        $this->mockSymbolFactory = $this->getMock(
            'Magento\CurrencySymbol\Model\System\CurrencysymbolFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->mockSymbol = $this->getMock(
            'Magento\CurrencySymbol\Model\System\Currencysymbol',
            ['getCurrencySymbol'],
            [],
            '',
            false
        );

        $this->mockEventObserver = $this->getMock(
            'Magento\Framework\Event\Observer',
            ['getEvent'],
            [],
            '',
            false
        );

        $this->mockEvent = $this->getMock(
            'Magento\Framework\Event',
            ['getBaseCode', 'getCurrencyOptions'],
            [],
            '',
            false
        );

        $this->mockEventObserver->expects($this->any())->method('getEvent')->willReturn($this->mockEvent);
        $this->mockSymbolFactory->expects($this->any())->method('create')->willReturn($this->mockSymbol);

        $this->observer = new \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions($this->mockSymbolFactory);
    }

    public function testCurrencyDisplayOptionsEmpty()
    {
        $sampleCurrencyOptionObject = new \Magento\Framework\DataObject;
        //Return invalid value
        $this->mockEvent->expects($this->once())->method('getBaseCode')->willReturn(null);
        $this->mockEvent->expects($this->once())->method('getCurrencyOptions')->willReturn($sampleCurrencyOptionObject);
        $this->mockSymbol->expects($this->never())->method('getCurrencySymbol')->with(null)->willReturn(null);

        $this->observer->execute($this->mockEventObserver);

        // Check if option set is empty
        $this->assertEquals([], $sampleCurrencyOptionObject->getData());
    }

    public function testCurrencyDisplayOptions()
    {
        $sampleCurrencyOptionObject = new \Magento\Framework\DataObject;
        $sampleCurrency = 'USD';
        $sampleCurrencySymbol = '$';

        $expectedCurrencyOptions = [
            \Magento\Framework\Locale\Currency::CURRENCY_OPTION_SYMBOL => $sampleCurrencySymbol,
            \Magento\Framework\Locale\Currency::CURRENCY_OPTION_DISPLAY => \Magento\Framework\Currency::USE_SYMBOL
        ];

        //Return invalid value
        $this->mockEvent->expects($this->once())->method('getBaseCode')->willReturn($sampleCurrency);
        $this->mockEvent->expects($this->once())->method('getCurrencyOptions')->willReturn($sampleCurrencyOptionObject);
        $this->mockSymbol->expects($this->once())
            ->method('getCurrencySymbol')
            ->with($sampleCurrency)
            ->willReturn($sampleCurrencySymbol);

        $this->observer->execute($this->mockEventObserver);

        // Check if option set is empty
        $this->assertEquals($expectedCurrencyOptions, $sampleCurrencyOptionObject->getData());
    }
}
