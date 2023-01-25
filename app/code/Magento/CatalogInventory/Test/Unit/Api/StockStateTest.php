<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Api;

use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockStateTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var StockStateProviderInterface|MockObject
     */
    protected $stockStateProvider;

    /**
     * @var StockRegistryProviderInterface|MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var StockInterface|MockObject
     */
    protected $stock;

    /**
     * @var StockItemInterface|MockObject
     */
    protected $stockItem;

    /**
     * @var StockStatusInterface|MockObject
     */
    protected $stockStatus;

    /**
     * @var DataObject|MockObject
     */
    protected $objectResult;

    protected $productId = 111;
    protected $websiteId = 111;
    protected $qty = 111;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stock = $this->getMockForAbstractClass(StockInterface::class);
        $this->stockItem = $this->getMockForAbstractClass(StockItemInterface::class);
        $this->stockStatus = $this->getMockForAbstractClass(StockStatusInterface::class);
        $this->objectResult = $this->createMock(DataObject::class);

        $this->stockStateProvider = $this->createPartialMock(
            StockStateProviderInterface::class,
            [
                'verifyStock',
                'verifyNotification',
                'checkQty',
                'suggestQty',
                'getStockQty',
                'checkQtyIncrements',
                'checkQuoteItemQty'
            ]
        );
        $this->stockStateProvider->expects($this->any())->method('verifyStock')->willReturn(true);
        $this->stockStateProvider->expects($this->any())->method('verifyNotification')->willReturn(true);
        $this->stockStateProvider->expects($this->any())->method('checkQty')->willReturn(true);
        $this->stockStateProvider->expects($this->any())->method('suggestQty')->willReturn($this->qty);
        $this->stockStateProvider->expects($this->any())->method('getStockQty')->willReturn($this->qty);
        $this->stockStateProvider->expects($this->any())->method('checkQtyIncrements')->willReturn($this->objectResult);
        $this->stockStateProvider->expects($this->any())->method('checkQuoteItemQty')->willReturn($this->objectResult);

        $this->stockRegistryProvider = $this->createPartialMock(
            StockRegistryProviderInterface::class,
            ['getStock', 'getStockItem', 'getStockStatus']
        );
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStock')
            ->willReturn($this->stock);
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItem);
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($this->stockStatus);

        $this->stockState = $this->objectManagerHelper->getObject(
            StockState::class,
            [
                'stockStateProvider' => $this->stockStateProvider,
                'stockRegistryProvider' => $this->stockRegistryProvider
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->stockState = null;
    }

    public function testVerifyStock()
    {
        $this->assertTrue(
            $this->stockState->verifyStock($this->productId, $this->websiteId)
        );
    }

    public function testVerifyNotification()
    {
        $this->assertTrue(
            $this->stockState->verifyNotification($this->productId, $this->websiteId)
        );
    }

    public function testCheckQty()
    {
        $this->assertTrue(
            $this->stockState->checkQty($this->productId, $this->qty, $this->websiteId)
        );
    }

    public function testSuggestQty()
    {
        $this->assertEquals(
            $this->qty,
            $this->stockState->suggestQty($this->productId, $this->qty, $this->websiteId)
        );
    }

    public function testGetStockQty()
    {
        $this->assertEquals(
            $this->qty,
            $this->stockState->getStockQty($this->productId, $this->websiteId)
        );
    }

    public function testCheckQtyIncrements()
    {
        $this->assertEquals(
            $this->objectResult,
            $this->stockState->checkQtyIncrements($this->productId, $this->qty, $this->websiteId)
        );
    }

    public function testCheckQuoteItemQty()
    {
        $this->assertEquals(
            $this->objectResult,
            $this->stockState->checkQuoteItemQty(
                $this->productId,
                $this->qty,
                $this->qty,
                $this->qty,
                $this->websiteId
            )
        );
    }
}
