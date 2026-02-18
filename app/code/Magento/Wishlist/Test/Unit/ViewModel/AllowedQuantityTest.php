<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Store\Model\Store;
use Magento\Wishlist\ViewModel\AllowedQuantity;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AllowedQuantityTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var AllowedQuantity
     */
    private $sut;

    /**
     * @var StockRegistry|MockObject
     */
    private $stockRegistryMock;

    /**
     * @var ItemInterface|MockObject
     */
    private $itemMock;

    /**
     * @var StockItemInterface|MockObject
     */
    private $stockItemMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->stockRegistryMock = $this->createMock(StockRegistry::class);
        $this->itemMock = $this->createMock(ItemInterface::class);
        $this->stockItemMock = $this->createMock(StockItemInterface::class);
        $this->productMock = $this->createMock(Product::class);
        $this->storeMock = $this->createMock(Store::class);

        $this->sut = new AllowedQuantity(
            $this->stockRegistryMock
        );
        $this->sut->setItem($this->itemMock);
    }

    /**
     * Getting min and max qty test.
     *
     * @param int $minSaleQty
     * @param int $maxSaleQty
     * @param array $expectedResult
     */
    #[DataProvider('saleQuantityDataProvider')]
    public function testGettingMinMaxQty(int $minSaleQty, int $maxSaleQty, array $expectedResult)
    {
        $this->storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->stockItemMock->expects($this->any())
            ->method('getMinSaleQty')
            ->willReturn($minSaleQty);
        $this->stockItemMock->expects($this->any())
            ->method('getMaxSaleQty')
            ->willReturn($maxSaleQty);
        $this->stockRegistryMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $result = $this->sut->getMinMaxQty();

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Sales quantity provider
     *
     * @return array
     */
    public static function saleQuantityDataProvider(): array
    {
        return [
            [
                1,
                10,
                [
                    'minAllowed' => 1,
                    'maxAllowed' => 10
                ]
            ], [
                1,
                0,
                [
                    'minAllowed' => 1,
                    'maxAllowed' => 99999999
                ]
            ]
        ];
    }
}
