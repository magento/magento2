<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Sales\Observer\Backend\CatalogProductSaveAfterObserver;

class CatalogProductSaveAfterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CatalogProductSaveAfterObserver
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
        $this->_quoteMock = $this->createMock(\Magento\Quote\Model\ResourceModel\Quote::class);
        $this->_observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getProduct', 'getStatus', 'getProductId']
        );
        $this->_observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->_eventMock));
        $this->_model = new CatalogProductSaveAfterObserver($this->_quoteMock);
    }

    /**
     * @param int $productId
     * @param int $productStatus
     * @dataProvider statusUpdateDataProvider
     */
    public function testSaveProduct($productId, $productStatus)
    {
        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'getStatus', '__wakeup']
        );
        $this->_eventMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $productMock->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $productMock->expects($this->once())->method('getStatus')->will($this->returnValue($productStatus));
        $this->_quoteMock->expects($this->any())->method('markQuotesRecollect');
        $this->_model->execute($this->_observerMock);
    }

    /**
     * @return array
     */
    public function statusUpdateDataProvider()
    {
        return [[125, 1], [100, 0]];
    }
}
