<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Test\Unit\Model\Options\DataProvider;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\ConfigurableProductGraphQl\Model\Options\DataProvider\Variant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the stock filter follows the "Display Out of Stock Products" setting.
 */
class VariantTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var Status|MockObject
     */
    private $stockStatusResource;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfig;

    /**
     * @var Variant
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $this->collection->method('addAttributeToSelect')->willReturnSelf();
        $this->collection->method('addFilterByRequiredOptions')->willReturnSelf();
        $this->collection->method('addMediaGalleryData')->willReturnSelf();
        $this->collection->method('addTierPriceData')->willReturnSelf();
        $this->collection->method('getItems')->willReturn([]);

        $configurableType = $this->createMock(Configurable::class);
        $configurableType->method('getUsedProductCollection')->willReturn($this->collection);

        $this->stockStatusResource = $this->createMock(Status::class);
        $stockStatusFactory = $this->createMock(StatusFactory::class);
        $stockStatusFactory->method('create')->willReturn($this->stockStatusResource);

        $this->stockConfig = $this->getMockForAbstractClass(StockConfigurationInterface::class);

        $this->model = new Variant($configurableType, $stockStatusFactory, $this->stockConfig);
    }

    /**
     * @param bool $showOutOfStock
     * @param bool $expectedFilterInStock
     * @return void
     */
    #[DataProvider('showOutOfStockDataProvider')]
    public function testStockFilterFollowsShowOutOfStockSetting(bool $showOutOfStock, bool $expectedFilterInStock): void
    {
        $this->stockConfig->method('isShowOutOfStock')->willReturn($showOutOfStock);
        $this->collection->method('hasFlag')->with('has_stock_status_filter')->willReturn(false);
        $this->collection->expects($this->once())->method('setFlag')->with('has_stock_status_filter', true);
        $this->stockStatusResource->expects($this->once())
            ->method('addStockDataToCollection')
            ->with($this->collection, $expectedFilterInStock);

        $this->model->getSalableVariantsByParent($this->createMock(Product::class));
    }

    /**
     * @return array
     */
    public static function showOutOfStockDataProvider(): array
    {
        return [
            'hide out of stock' => [false, true],
            'show out of stock' => [true, false],
        ];
    }

    public function testStockDataNotAddedTwiceToAlreadyMarkedCollection(): void
    {
        $this->collection->method('hasFlag')->with('has_stock_status_filter')->willReturn(true);
        $this->collection->expects($this->never())->method('setFlag');
        $this->stockStatusResource->expects($this->never())->method('addStockDataToCollection');
        $this->stockConfig->expects($this->never())->method('isShowOutOfStock');

        $this->model->getSalableVariantsByParent($this->createMock(Product::class));
    }
}
