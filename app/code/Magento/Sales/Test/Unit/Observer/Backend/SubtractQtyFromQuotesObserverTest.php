<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Sales\Observer\Backend\SubtractQtyFromQuotesObserver;

class SubtractQtyFromQuotesObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubtractQtyFromQuotesObserver
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventMock;

    protected function setUp(): void
    {
        $this->_quoteMock = $this->createMock(\Magento\Quote\Model\ResourceModel\Quote::class);
        $this->_observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getProduct', 'getStatus', 'getProductId']
        );
        $this->_observerMock->expects($this->any())->method('getEvent')->willReturn($this->_eventMock);
        $this->_model = new SubtractQtyFromQuotesObserver($this->_quoteMock);
    }

    public function testSubtractQtyFromQuotes()
    {
        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'getStatus', '__wakeup']
        );
        $this->_eventMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $this->_quoteMock->expects($this->once())->method('subtractProductFromQuotes')->with($productMock);
        $this->_model->execute($this->_observerMock);
    }
}
