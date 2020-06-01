<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Observer;

use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\CurrencySymbol\Model\System\CurrencysymbolFactory;
use Magento\CurrencySymbol\Observer\CurrencyDisplayOptions;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Locale\Currency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CurrencySymbol\Observer\CurrencyDisplayOptions
 */
class CurrencyDisplayOptionsTest extends TestCase
{
    /**
     * @var CurrencyDisplayOptions
     */
    private $observer;

    /**
     * @var MockObject|CurrencysymbolFactory $mockSymbolFactory
     */
    private $mockSymbolFactory;

    /**
     * @var MockObject|Currencysymbol $mockSymbol
     */
    private $mockSymbol;

    /**
     * @var MockObject|Observer $mockEvent
     */
    private $mockEventObserver;

    /**
     * @var MockObject|Event $mockEvent
     */
    private $mockEvent;

    protected function setUp(): void
    {
        $this->mockSymbolFactory = $this->createPartialMock(
            CurrencysymbolFactory::class,
            ['create']
        );

        $this->mockSymbol = $this->createPartialMock(
            Currencysymbol::class,
            ['getCurrencySymbol']
        );

        $this->mockEventObserver = $this->createPartialMock(Observer::class, ['getEvent']);

        $this->mockEvent = $this->getMockBuilder(Event::class)
            ->addMethods(['getBaseCode', 'getCurrencyOptions'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockEventObserver->expects($this->any())->method('getEvent')->willReturn($this->mockEvent);
        $this->mockSymbolFactory->expects($this->any())->method('create')->willReturn($this->mockSymbol);

        $this->observer = new CurrencyDisplayOptions($this->mockSymbolFactory);
    }

    public function testCurrencyDisplayOptionsEmpty()
    {
        $baseData = [
            Currency::CURRENCY_OPTION_NAME => 'US Dollar'
        ];
        $sampleCurrencyOptionObject = new DataObject($baseData);

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
            Currency::CURRENCY_OPTION_NAME => 'US Dollar'
        ];
        $sampleCurrencyOptionObject = new DataObject($baseData);
        $sampleCurrency = 'USD';
        $sampleCurrencySymbol = '$';

        $expectedCurrencyOptions = array_merge(
            $baseData,
            [
                Currency::CURRENCY_OPTION_NAME => 'US Dollar',
                Currency::CURRENCY_OPTION_SYMBOL => $sampleCurrencySymbol,
                Currency::CURRENCY_OPTION_DISPLAY => \Magento\Framework\Currency::USE_SYMBOL
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
