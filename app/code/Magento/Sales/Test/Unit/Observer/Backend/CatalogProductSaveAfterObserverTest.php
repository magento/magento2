<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Observer\Backend\CatalogProductSaveAfterObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class CatalogProductSaveAfterObserverTest extends TestCase
{
    use MockCreationTrait;

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
        $this->_eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getProduct', 'getStatus', 'getProductId']
        );
        $this->_observerMock->expects($this->any())->method('getEvent')->willReturn($this->_eventMock);
        $this->_model = new CatalogProductSaveAfterObserver($this->_quoteMock);
    }

    /**
     * @param int $productId
     * @param int $productStatus
     */
    #[DataProvider('statusUpdateDataProvider')]
    public function testSaveProduct($productId, $productStatus)
    {
        $productMock = $this->createPartialMock(
            Product::class,
            ['getId', 'getStatus']
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
    public static function statusUpdateDataProvider()
    {
        return [[125, 1], [100, 0]];
    }
}
