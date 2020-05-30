<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Observer\Backend\SubtractQtyFromQuotesObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubtractQtyFromQuotesObserverTest extends TestCase
{
    /**
     * @var SubtractQtyFromQuotesObserver
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_quoteMock;

    /**
     * @var MockObject
     */
    protected $_observerMock;

    /**
     * @var MockObject
     */
    protected $_eventMock;

    protected function setUp(): void
    {
        $this->_quoteMock = $this->createMock(Quote::class);
        $this->_observerMock = $this->createMock(Observer::class);
        $this->_eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getProduct', 'getStatus', 'getProductId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_observerMock->expects($this->any())->method('getEvent')->willReturn($this->_eventMock);
        $this->_model = new SubtractQtyFromQuotesObserver($this->_quoteMock);
    }

    public function testSubtractQtyFromQuotes()
    {
        $productMock = $this->createPartialMock(
            Product::class,
            ['getId', 'getStatus']
        );
        $this->_eventMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $this->_quoteMock->expects($this->once())->method('subtractProductFromQuotes')->with($productMock);
        $this->_model->execute($this->_observerMock);
    }
}
