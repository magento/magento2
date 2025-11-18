<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Test\Unit\Model\Resolver;

use PHPUnit\Framework\TestCase;
use Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for \Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver
 */
class OnlyXLeftInStockResolverTest extends TestCase
{
    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfigMock;

    /** @var StockRegistryInterface|MockObject */
    private $stockRegistryMock;

    /** @var Product|MockObject */
    private $productModelMock;

    /** @var StockItemInterface|MockObject */
    private $stockItemMock;

    /** @var StockStatusInterface|MockObject */
    private $stockStatusMock;

    /** @var Field|MockObject */
    private $fieldMock;

    /** @var ResolveInfo|MockObject */
    private $resolveInfoMock;

    /** @var OnlyXLeftInStockResolver */
    private $resolver;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->stockRegistryMock = $this->createMock(StockRegistryInterface::class);
        $productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->productModelMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productModelMock->method('getId')->willReturn(1);

        $storeMock = new class
        {
            public function getWebsiteId()
            {
                return 1;
            }
        };
        $this->productModelMock->method('getStore')->willReturn($storeMock);

        $this->stockItemMock = $this->createMock(StockItemInterface::class);
        $this->stockStatusMock = $this->createMock(StockStatusInterface::class);

        $this->stockRegistryMock->method('getStockStatus')->willReturn($this->stockStatusMock);

        /** NEW: Required field + resolveInfo mocks **/
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->resolver = $objectManager->getObject(
            OnlyXLeftInStockResolver::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'stockRegistry' => $this->stockRegistryMock,
                'productRepositoryInterface' => $productRepository
            ]
        );
    }

    /**
     * Helper: call resolver with the required mocked args
     */
    private function resolveWithProduct($product)
    {
        return $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            ['model' => $product]
        );
    }

    public function testResolve()
    {
        $stockCurrentQty = 3;
        $minQty = 2;
        $thresholdQty = 1;

        $this->stockItemMock->expects($this->once())->method('getMinQty')->willReturn($minQty);
        $this->stockStatusMock->expects($this->once())->method('getQty')->willReturn($stockCurrentQty);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->willReturn($this->stockItemMock);
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            $stockCurrentQty,
            $this->resolveWithProduct($this->productModelMock)
        );
    }

    public function testResolveOutStock()
    {
        $stockCurrentQty = 0;
        $minQty = 2;
        $thresholdQty = 1;

        $this->stockItemMock->expects($this->once())->method('getMinQty')->willReturn($minQty);
        $this->stockStatusMock->expects($this->once())->method('getQty')->willReturn($stockCurrentQty);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->willReturn($this->stockItemMock);
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            0,
            $this->resolveWithProduct($this->productModelMock)
        );
    }

    public function testResolveNoThresholdQty()
    {
        $thresholdQty = null;
        $this->stockItemMock->expects($this->never())->method('getMinQty');
        $this->stockStatusMock->expects($this->never())->method('getQty');
        $this->stockRegistryMock->expects($this->never())->method('getStockItem');
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertNull($this->resolveWithProduct($this->productModelMock));
    }

    public function testResolveConfigurableProductWithNoVariants()
    {
        $thresholdQty = 1;

        $configurableProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurableProduct->expects($this->once())->method('getTypeId')->willReturn('configurable');

        $configurableTypeMock = new class
        {
            public function getUsedProducts($product)
            {
                return [];
            }
        };

        $configurableProduct->expects($this->once())->method('getTypeInstance')->willReturn($configurableTypeMock);

        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertNull(
            $this->resolveWithProduct($configurableProduct)
        );
    }

    public function testResolveSimpleProductRegression()
    {
        $stockCurrentQty = 8;
        $minQty = 2;
        $thresholdQty = 10;

        $this->productModelMock->expects($this->once())->method('getTypeId')->willReturn('simple');

        $this->stockItemMock->expects($this->once())->method('getMinQty')->willReturn($minQty);
        $this->stockStatusMock->expects($this->once())->method('getQty')->willReturn($stockCurrentQty);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->willReturn($this->stockItemMock);
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            $stockCurrentQty,
            $this->resolveWithProduct($this->productModelMock)
        );
    }

    public function testResolveBasicStockThresholdLogic()
    {
        $stockCurrentQty = 15;
        $minQty = 5;
        $thresholdQty = 20;

        $this->stockItemMock->expects($this->once())->method('getMinQty')->willReturn($minQty);
        $this->stockStatusMock->expects($this->once())->method('getQty')->willReturn($stockCurrentQty);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->willReturn($this->stockItemMock);
        $this->scopeConfigMock->method('getValue')->willReturn($thresholdQty);

        $this->assertEquals(
            $stockCurrentQty,
            $this->resolveWithProduct($this->productModelMock)
        );
    }
}
