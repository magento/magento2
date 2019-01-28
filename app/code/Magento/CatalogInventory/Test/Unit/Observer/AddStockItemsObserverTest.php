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
     * @var StockItemCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaInterfaceFactoryMock;

    /**
     * @var StockItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItemRepositoryMock;

    /**
     * @var StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockConfigurationMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
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
        $criteria->expects($this->once())
            ->method('setProductsFilter')
            ->with($this->identicalTo([$productId]))
            ->willReturn(true);
        $criteria->expects($this->once())
            ->method('setScopeFilter')
            ->with($this->identicalTo($defaultScopeId))
            ->willReturn(true);

        $this->criteriaInterfaceFactoryMock->expects($this->once())
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
        $stockItem->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $stockItemCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$stockItem]);

        $this->stockItemRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->identicalTo($criteria))
            ->willReturn($stockItemCollection);

        $this->stockConfigurationMock->expects($this->once())
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);

        $productExtension = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['setStockItem'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productExtension->expects($this->once())
            ->method('setStockItem')
            ->with($this->identicalTo($stockItem));

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtension);
        $product->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->identicalTo($productExtension))
            ->willReturnSelf();

        /** @var ProductCollection|\PHPUnit_Framework_MockObject_MockObject $productCollection */
        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$productId => $product]);
        $productCollection->expects($this->once())
            ->method('getItemById')
            ->with($this->identicalTo($productId))
            ->willReturn($product);

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $observer->expects($this->once())
            ->method('getData')
            ->with('collection')
            ->willReturn($productCollection);

        $this->subject->execute($observer);
    }
}
