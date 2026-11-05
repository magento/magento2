<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Test\Unit\Model\Resolver;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Store\Api\Data\StoreInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;

/**
 * Test class for \Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver
 */
class OnlyXLeftInStockResolverTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var OnlyXLeftInStockResolver
     */
    private $resolver;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private $stockRegistryMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var Product|MockObject
     */
    private $productModelMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var StockItemInterface|MockObject
     */
    private $stockItemMock;

    /**
     * @var StockStatusInterface|MockObject
     */
    private $stockStatusMock;

    /**
     * @inheritdoc
     */

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->productModelMock = $this->createMock(Product::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->stockRegistryMock = $this->createMock(StockRegistryInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->stockItemMock = $this->createMock(StockItemInterface::class);
        $this->stockStatusMock = $this->createMock(StockStatusInterface::class);

        $this->productModelMock->method('getId')->willReturn(1);
        $this->productModelMock->expects($this->atMost(1))->method('getStore')
            ->willReturn($this->storeMock);
        $this->stockRegistryMock->expects($this->atMost(1))->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $this->storeMock->expects($this->atMost(1))->method('getWebsiteId')->willReturn(1);

        $this->resolver = new OnlyXLeftInStockResolver(
            $this->scopeConfigMock,
            $this->stockRegistryMock,
            $this->productRepositoryMock
        );
    }

    public function testResolve()
    {
        $stockCurrentQty = 3;
        $minQty = 2;
        $thresholdQty = 1;

        $this->stockItemMock->expects($this->once())->method('getMinQty')
            ->willReturn($minQty);
        $this->stockStatusMock->expects($this->once())->method('getQty')
            ->willReturn($stockCurrentQty);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            $stockCurrentQty,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                ['model' => $this->productModelMock]
            )
        );
    }

    public function testResolveOutStock()
    {
        $stockCurrentQty = 0;
        $minQty = 2;
        $thresholdQty = 1;
        $this->stockItemMock->expects($this->once())->method('getMinQty')
            ->willReturn($minQty);
        $this->stockStatusMock->expects($this->once())->method('getQty')
            ->willReturn($stockCurrentQty);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            0,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                ['model' => $this->productModelMock]
            )
        );
    }

    public function testResolveNoThresholdQty()
    {
        $thresholdQty = null;
        $this->stockItemMock->expects($this->never())->method('getMinQty');
        $this->stockStatusMock->expects($this->never())->method('getQty');
        $this->stockRegistryMock->expects($this->never())->method('getStockItem');
        $this->productRepositoryMock->expects($this->never())->method('get');
        $this->productModelMock->expects($this->never())->method('getTypeId');
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            null,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                ['model' => $this->productModelMock]
            )
        );
    }

    public function testResolveConfigurableWithoutSelectedVariant()
    {
        $this->scopeConfigMock->method('getValue')->willReturn(200);
        $this->productModelMock->method('getTypeId')->willReturn('configurable');
        $this->productModelMock->method('getCustomOption')->with('simple_product')->willReturn(null);
        $this->productModelMock->method('getSku')->willReturn('parent_configurable');
        $this->productModelMock->method('getData')->with('sku')->willReturn('parent_configurable');
        $this->productRepositoryMock->expects($this->never())->method('get');
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->with(1)
            ->willReturn($this->stockItemMock);
        $this->stockItemMock->method('getMinQty')->willReturn(0);
        $this->stockStatusMock->method('getQty')->willReturn(100);

        $this->assertEquals(
            100,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                ['model' => $this->productModelMock]
            )
        );
    }

    public function testResolveConfigurableUsesSimpleProductOption()
    {
        $variantMock = $this->createMock(Product::class);
        $variantMock->method('getId')->willReturn(42);
        $variantMock->method('getStore')->willReturn($this->storeMock);

        $optionMock = $this->createMock(Option::class);
        $optionMock->method('getProduct')->willReturn($variantMock);

        $this->scopeConfigMock->method('getValue')->willReturn(200);
        $this->productModelMock->method('getTypeId')->willReturn('configurable');
        $this->productModelMock->method('getCustomOption')->with('simple_product')->willReturn($optionMock);
        $this->productRepositoryMock->expects($this->never())->method('get');
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->with(42)
            ->willReturn($this->stockItemMock);
        $this->stockItemMock->method('getMinQty')->willReturn(0);
        $this->stockStatusMock->method('getQty')->willReturn(7);

        $this->assertEquals(
            7,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                ['model' => $this->productModelMock]
            )
        );
    }
}
