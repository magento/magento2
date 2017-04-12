<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Sales\Observer\Backend\SubtractQtyFromQuotesObserver;

class SubtractQtyFromQuotesObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubtractQtyFromQuotesObserver
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventMock;

    protected function setUp()
    {
        $this->_quoteMock = $this->getMock(\Magento\Quote\Model\ResourceModel\Quote::class, [], [], '', false);
        $this->_observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->_eventMock = $this->getMock(
            \Magento\Framework\Event::class,
            ['getProduct', 'getStatus', 'getProductId'],
            [],
            '',
            false
        );
        $this->_observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->_eventMock));
        $this->_model = new SubtractQtyFromQuotesObserver($this->_quoteMock);
    }

    public function testSubtractQtyFromQuotes()
    {
        $productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'getStatus', '__wakeup'],
            [],
            '',
            false
        );
        $this->_eventMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $this->_quoteMock->expects($this->once())->method('substractProductFromQuotes')->with($productMock);
        $this->_model->execute($this->_observerMock);
    }
}
