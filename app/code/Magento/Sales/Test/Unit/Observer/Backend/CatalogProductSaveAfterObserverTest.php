<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Observer\Backend\CatalogProductSaveAfterObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogProductSaveAfterObserverTest extends TestCase
{
    /**
     * @var CatalogProductSaveAfterObserver
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
        $this->_eventMock = $this->createPartialMock(
            Event::class,
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
            Product::class,
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
