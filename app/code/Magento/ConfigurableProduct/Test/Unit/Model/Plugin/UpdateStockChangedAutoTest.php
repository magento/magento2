<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\CatalogInventory\Model\Stock;
use Magento\ConfigurableProduct\Model\Plugin\UpdateStockChangedAuto;
use Magento\Catalog\Model\ResourceModel\GetProductTypeById;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit test for Magento\ConfigurableProduct\Model\Plugin\UpdateStockChangedAuto class.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateStockChangedAutoTest extends TestCase
{
    use MockCreationTrait;

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
        $this->getProductTypeByIdMock = $this->createMock(GetProductTypeById::class);
        $this->plugin = new UpdateStockChangedAuto($this->getProductTypeByIdMock);
    }

    /**
     * Verify before Stock Item save. Negative scenario
     *
     * @return void
     */
    public function testBeforeSaveForInStock()
    {
        $itemResourceModel = $this->createMock(ItemResourceModel::class);
        $stockItem = $this->createPartialMockWithReflection(
            StockItem::class,
            ['getIsInStock']
        );
        $stockItem->method('getIsInStock')->willReturn(true);
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
        $itemResourceModel = $this->createMock(ItemResourceModel::class);
        $stockItem = $this->createPartialMockWithReflection(
            StockItem::class,
            ['getIsInStock', 'hasStockStatusChangedAutomaticallyFlag', 'getProductId', 'setStockStatusChangedAuto']
        );
        $stockItem->method('getIsInStock')->willReturn(false);
        $stockItem->method('hasStockStatusChangedAutomaticallyFlag')->willReturn(false);
        $stockItem->method('getProductId')->willReturn($productId);
        $this->getProductTypeByIdMock->expects(self::once())
            ->method('execute')
            ->with($productId)
            ->willReturn($productType);
        $this->plugin->beforeSave($itemResourceModel, $stockItem);
    }
}
