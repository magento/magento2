<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Observer\AddStockItemsObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AddStockItemsObserverTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var AddStockItemsObserver
     */
    private $subject;
    /**
     * @var StockItemCriteriaInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $criteriaInterfaceFactoryMock;

    /**
     * @var StockItemRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stockItemRepositoryMock;

    /**
     * @var StockConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stockConfigurationMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->criteriaInterfaceFactoryMock = $this->getMockBuilder(StockItemCriteriaInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockItemRepositoryMock = $this->getMockBuilder(StockItemRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->setMethods(['getDefaultScopeId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->subject = $objectManager->getObject(
            AddStockItemsObserver::class,
            [
                'criteriaInterfaceFactory' => $this->criteriaInterfaceFactoryMock,
                'stockItemRepository' => $this->stockItemRepositoryMock,
                'stockConfiguration' => $this->stockConfigurationMock
            ]
        );
    }

    /**
     * Test AddStockItemsObserver::execute() add stock item to product as extension attribute.
     */
    public function testExecute()
    {
        $productId = 1;
        $defaultScopeId = 0;

        $criteria = $this->getMockBuilder(StockItemCriteriaInterface::class)
            ->setMethods(['setProductsFilter', 'setScopeFilter'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $criteria->expects(self::once())
            ->method('setProductsFilter')
            ->with(self::identicalTo([$productId]))
            ->willReturn(true);
        $criteria->expects(self::once())
            ->method('setScopeFilter')
            ->with(self::identicalTo($defaultScopeId))
            ->willReturn(true);

        $this->criteriaInterfaceFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($criteria);
        $stockItemCollection = $this->getMockBuilder(StockItemCollectionInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['getProductId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $stockItem->expects(self::once())
            ->method('getProductId')
            ->willReturn($productId);

        $stockItemCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$stockItem]);

        $this->stockItemRepositoryMock->expects(self::once())
            ->method('getList')
            ->with(self::identicalTo($criteria))
            ->willReturn($stockItemCollection);

        $this->stockConfigurationMock->expects(self::once())
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);

        $productExtension = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['setStockItem'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productExtension->expects(self::once())
            ->method('setStockItem')
            ->with(self::identicalTo($stockItem));

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects(self::once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtension);
        $product->expects(self::once())
            ->method('setExtensionAttributes')
            ->with(self::identicalTo($productExtension))
            ->willReturnSelf();

        /** @var ProductCollection|\PHPUnit\Framework\MockObject\MockObject $productCollection */
        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$productId => $product]);
        $productCollection->expects(self::once())
            ->method('getItemById')
            ->with(self::identicalTo($productId))
            ->willReturn($product);

        /** @var Observer|\PHPUnit\Framework\MockObject\MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $observer->expects(self::once())
            ->method('getData')
            ->with('collection')
            ->willReturn($productCollection);

        $this->subject->execute($observer);
    }
}
