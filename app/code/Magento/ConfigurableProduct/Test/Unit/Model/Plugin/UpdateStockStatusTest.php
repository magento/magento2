<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\ConfigurableProduct\Model\Plugin\UpdateStockStatus;
use Magento\Catalog\Api\GetProductTypeByIdInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterface as StockItem;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\ConfigurableProduct\Model\Plugin\UpdateStockStatus class.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateStockStatusTest  extends TestCase
{
    /**
     * @var MockObject
     */
    private $getProductTypeByIdMock;

    /**
     * @var MockObject
     */
    protected $stockStatusRepositoryMock;

    /**
     * @var UpdateStockStatus
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getProductTypeByIdMock = $this->getMockForAbstractClass(GetProductTypeByIdInterface::class);
        $this->stockStatusRepositoryMock = $this->getMockForAbstractClass(
            StockStatusRepositoryInterface::class
        );
        $this->plugin = new UpdateStockStatus($this->getProductTypeByIdMock, $this->stockStatusRepositoryMock);
    }

    /**
     * Verify before Stock Item save
     *
     * @return void
     */
    public function testBeforeSaveForOutOfStock()
    {
        $itemResourceModel = $this->getMockBuilder(ItemResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem = $this->getMockForAbstractClass(StockItem::class);
        $stockItem->expects(self::once())
            ->method('getIsInStock')
            ->willReturn(Stock::STOCK_OUT_OF_STOCK);
        $this->getProductTypeByIdMock->expects(self::never())->method('execute');
        $this->plugin->beforeSave($itemResourceModel, $stockItem);
    }

    public function testBeforeSaveForConfigurableInStock()
    {
        $productType = Configurable::TYPE_CODE;
        $productId = 1;
        $itemResourceModel = $this->getMockBuilder(ItemResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem = $this->getMockForAbstractClass(StockItem::class);
        $stockItem->expects(self::once())
            ->method('getIsInStock')
            ->willReturn(Stock::STOCK_IN_STOCK);
        $stockItem->expects(self::once())
            ->method('getStockStatusChangedAuto')
            ->willReturn(true);
        $stockItem->expects($this->exactly(2))
            ->method('getProductId')
            ->willReturn($productId);
        $this->getProductTypeByIdMock->expects(self::once())
            ->method('execute')
            ->with($productId)
            ->willReturn($productType);
        $stockStatusMock = $this->getMockForAbstractClass(StockStatusInterface::class);
        $stockStatusMock->expects(static::once())
            ->method('setStockStatus')
            ->with(Stock::STOCK_IN_STOCK);
        $this->stockStatusRepositoryMock->expects(static::atLeastOnce())
            ->method('get')
            ->with($productId)
            ->willReturn($stockStatusMock);
        $this->stockStatusRepositoryMock->expects(self::once())->method('save');

        $this->plugin->beforeSave($itemResourceModel, $stockItem);
    }
}
