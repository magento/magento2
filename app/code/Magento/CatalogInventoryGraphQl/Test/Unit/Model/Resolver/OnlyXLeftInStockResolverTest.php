<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Test\Unit\Model\Resolver;

use PHPUnit\Framework\TestCase;
use Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Api\Data\StoreInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;

/**
 * Test class for \Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver
 */
class OnlyXLeftInStockResolverTest extends TestCase
{
    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var RevokeCustomerToken
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
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productModelMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->stockRegistryMock = $this->getMockBuilder(StockRegistryInterface::class)->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)->getMock();
        $this->stockItemMock = $this->getMockBuilder(StockItemInterface::class)->getMock();
        $this->stockStatusMock = $this->getMockBuilder(StockStatusInterface::class)->getMock();
        $this->productModelMock->expects($this->any())->method('getId')
            ->willReturn(1);
        $this->productModelMock->expects($this->atMost(1))->method('getStore')
            ->willReturn($this->storeMock);
        $this->stockRegistryMock->expects($this->atMost(1))->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $this->storeMock->expects($this->atMost(1))->method('getWebsiteId')->willReturn(1);

        $this->resolver = $this->objectManager->getObject(
            OnlyXLeftInStockResolver::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'stockRegistry' => $this->stockRegistryMock
            ]
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
}
