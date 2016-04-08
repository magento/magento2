<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Sales\Observer\Backend\CatalogProductSaveAfterObserver;

class CatalogProductSaveAfterObserverTest extends \PHPUnit_Framework_TestCase
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
        $this->_quoteMock = $this->getMock('Magento\Quote\Model\ResourceModel\Quote', [], [], '', false);
        $this->_observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->_eventMock = $this->getMock(
            'Magento\Framework\Event',
            ['getProduct', 'getStatus', 'getProductId'],
            [],
            '',
            false
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
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getStatus', '__wakeup'],
            [],
            '',
            false
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
