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
        $this->_eventMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $productMock->expects($this->once())->method('getStatus')->willReturn($productStatus);
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
