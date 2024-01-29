<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\CatalogInventory\Model\Stock;
use Magento\ConfigurableProduct\Model\Plugin\UpdateStockChangedAuto;
use Magento\Catalog\Model\ResourceModel\GetProductTypeById;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\Framework\Model\AbstractModel as StockItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\ConfigurableProduct\Model\Plugin\UpdateStockChangedAuto class.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateStockChangedAutoTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $getProductTypeByIdMock;

    /**
     * @var UpdateStockChangedAuto
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getProductTypeByIdMock = $this->getMockBuilder(GetProductTypeById::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin = new UpdateStockChangedAuto($this->getProductTypeByIdMock);
    }

    /**
     * Verify before Stock Item save. Negative scenario
     *
     * @return void
     */
    public function testBeforeSaveForInStock()
    {
        $itemResourceModel = $this->getMockBuilder(ItemResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem = $this->getMockBuilder(StockItem::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsInStock', 'setStockStatusChangedAuto'])
            ->getMock();
        $stockItem->expects(self::once())
            ->method('getIsInStock')
            ->willReturn(Stock::STOCK_IN_STOCK);
        $stockItem->expects(self::never())->method('setStockStatusChangedAuto');
        $this->plugin->beforeSave($itemResourceModel, $stockItem);
    }

    /**
     * Verify before Stock Item save
     *
     * @return void
     */
    public function testBeforeSaveForConfigurableInStock()
    {
        $productType = Configurable::TYPE_CODE;
        $productId = 1;
        $itemResourceModel = $this->getMockBuilder(ItemResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem = $this->getMockBuilder(StockItem::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getIsInStock',
                'getProductId',
                'hasStockStatusChangedAutomaticallyFlag',
                'setStockStatusChangedAuto'
            ])
            ->getMock();
        $stockItem->expects(self::once())
            ->method('getIsInStock')
            ->willReturn(Stock::STOCK_OUT_OF_STOCK);
        $stockItem->expects(self::once())
            ->method('hasStockStatusChangedAutomaticallyFlag')
            ->willReturn(false);
        $stockItem->expects(self::once())
            ->method('getProductId')
            ->willReturn($productId);
        $this->getProductTypeByIdMock->expects(self::once())
            ->method('execute')
            ->with($productId)
            ->willReturn($productType);
        $stockItem->expects(self::once())->method('setStockStatusChangedAuto')->with(0);

        $this->plugin->beforeSave($itemResourceModel, $stockItem);
    }
}
