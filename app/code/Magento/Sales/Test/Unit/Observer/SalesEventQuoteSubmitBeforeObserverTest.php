<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer;

use Magento\Framework\DataObject\Copy\Config;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Observer\SalesEventQuoteSubmitBeforeObserver;

/**
 * Test for Magento\Sales\Observer\SalesEventQuoteSubmitBeforeObserver.
 */
class SalesEventQuoteSubmitBeforeObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SalesEventQuoteSubmitBeforeObserver
     */
    private $observer;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldsetConfig;

    protected function setUp()
    {
        $this->fieldsetConfig = $this->createPartialMock(Config::class, ['getFieldset']);

        $this->observer = new SalesEventQuoteSubmitBeforeObserver(
            $this->fieldsetConfig
        );
    }

    public function testExecute()
    {
        $fields = [
            'store_id' => ['to_order' => '*'],
            'test_field' => ['to_order' => '*'],
            'coupon_code' => ['to_order' => '*'],
            'base_subtotal_invoiced' => ['to_order' => '*'],
            'some_new' => ['to_order' => '*'],
        ];

        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getQuote', 'getOrder']);
        $orderMock = $this->createPartialMockForAbstractClass(OrderInterface::class, ['setData']);
        $quoteMock = $this->createPartialMockForAbstractClass(CartInterface::class, ['getData']);

        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $eventMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $this->fieldsetConfig->expects($this->once())->method('getFieldset')->with('sales_convert_quote', 'global')
            ->willReturn($fields);
        $quoteMock->expects($this->exactly(2))->method('getData')->withConsecutive(
            ['test_field'],
            ['some_new']
        )->willReturn('test_data');
        $orderMock->expects($this->exactly(2))->method('setData')->withConsecutive(
            ['test_field', 'test_data'],
            ['some_new', 'test_data']
        );

        $this->observer->execute($observerMock);
    }

    /**
     * Get mock for abstract class with methods.
     *
     * @param string $className
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createPartialMockForAbstractClass($className, $methods = [])
    {
        return $this->getMockForAbstractClass(
            $className,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}
