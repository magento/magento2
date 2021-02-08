<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Observer;

use Magento\CurrencySymbol\Model\System\CurrencysymbolFactory;

/**
 * Test for \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions
 */
class CurrencyDisplayOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions
     */
    private $observer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CurrencysymbolFactory $mockSymbolFactory
     */
    private $mockSymbolFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\CurrencySymbol\Model\System\Currencysymbol $mockSymbol
     */
    private $mockSymbol;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\Observer $mockEvent
     */
    private $mockEventObserver;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event $mockEvent
     */
    private $mockEvent;

    protected function setUp(): void
    {
        $this->mockSymbolFactory = $this->createPartialMock(
            \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory::class,
            ['create']
        );

        $this->mockSymbol = $this->createPartialMock(
            \Magento\CurrencySymbol\Model\System\Currencysymbol::class,
            ['getCurrencySymbol']
        );

        $this->mockEventObserver = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);

        $this->mockEvent = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getBaseCode', 'getCurrencyOptions']
        );

        $this->mockEventObserver->expects($this->any())->method('getEvent')->willReturn($this->mockEvent);
        $this->mockSymbolFactory->expects($this->any())->method('create')->willReturn($this->mockSymbol);

        $this->observer = new \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions($this->mockSymbolFactory);
    }

    public function testCurrencyDisplayOptionsEmpty()
    {
        $baseData = [
            \Magento\Framework\Locale\Currency::CURRENCY_OPTION_NAME => 'US Dollar'
        ];
        $sampleCurrencyOptionObject = new \Magento\Framework\DataObject($baseData);

        //Return invalid value
        $this->mockEvent->expects($this->once())->method('getBaseCode')->willReturn(null);
        $this->mockEvent->expects($this->once())->method('getCurrencyOptions')->willReturn($sampleCurrencyOptionObject);
        $this->mockSymbol->expects($this->never())->method('getCurrencySymbol')->with(null)->willReturn(null);

        $this->observer->execute($this->mockEventObserver);

        // Check if option set is empty
        $this->assertEquals($baseData, $sampleCurrencyOptionObject->getData());
    }

    public function testCurrencyDisplayOptions()
    {
        $baseData = [
            \Magento\Framework\Locale\Currency::CURRENCY_OPTION_NAME => 'US Dollar'
        ];
        $sampleCurrencyOptionObject = new \Magento\Framework\DataObject($baseData);
        $sampleCurrency = 'USD';
        $sampleCurrencySymbol = '$';

        $expectedCurrencyOptions = array_merge(
            $baseData,
            [
                \Magento\Framework\Locale\Currency::CURRENCY_OPTION_NAME => 'US Dollar',
                \Magento\Framework\Locale\Currency::CURRENCY_OPTION_SYMBOL => $sampleCurrencySymbol,
                \Magento\Framework\Locale\Currency::CURRENCY_OPTION_DISPLAY => \Magento\Framework\Currency::USE_SYMBOL
            ]
        );

        $this->mockEvent->expects($this->once())->method('getBaseCode')->willReturn($sampleCurrency);
        $this->mockEvent->expects($this->once())->method('getCurrencyOptions')->willReturn($sampleCurrencyOptionObject);
        $this->mockSymbol->expects($this->once())
            ->method('getCurrencySymbol')
            ->with($sampleCurrency)
            ->willReturn($sampleCurrencySymbol);

        $this->observer->execute($this->mockEventObserver);

        $this->assertEquals($expectedCurrencyOptions, $sampleCurrencyOptionObject->getData());
    }
}
