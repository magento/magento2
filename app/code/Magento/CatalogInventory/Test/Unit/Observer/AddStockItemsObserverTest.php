<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\CatalogInventory\Observer\AddStockItemsObserver;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddStockItemsObserverTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var AddStockItemsObserver
     */
    private $subject;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfigurationMock;
    /**
     * @var StockRegistryPreloader|MockObject
     */
    private $stockRegistryPreloader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->onlyMethods(['getDefaultScopeId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockRegistryPreloader = $this->createMock(StockRegistryPreloader::class);
        $this->subject = new AddStockItemsObserver(
            $this->stockConfigurationMock,
            $this->stockRegistryPreloader,
        );
    }

    /**
     * Test AddStockItemsObserver::execute() add stock item to product as extension attribute.
     */
    public function testExecute()
    {
        $productId = 1;
        $defaultScopeId = 0;

        $stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->onlyMethods(['getProductId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $stockItem->expects(self::once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->stockRegistryPreloader->expects(self::once())
            ->method('preloadStockItems')
            ->with([$productId])
            ->willReturn([$stockItem]);

        $this->stockRegistryPreloader->expects(self::once())
            ->method('preloadStockStatuses')
            ->with([$productId])
            ->willReturn([]);

        $this->stockRegistryPreloader->expects(self::once())
            ->method('preloadStockItems')
            ->willReturn([$stockItem]);

        $this->stockConfigurationMock->expects(self::once())
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);

        $productExtension = $this->getMockBuilder(ProductExtensionInterface::class)
            ->addMethods(['setStockItem'])
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

        /** @var ProductCollection|MockObject $productCollection */
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

        /** @var Observer|MockObject $observer */
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
