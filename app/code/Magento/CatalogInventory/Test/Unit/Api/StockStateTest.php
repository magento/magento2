<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StockStateTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockStateTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockStateProvider;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockStatus;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectResult;

    protected $productId = 111;
    protected $websiteId = 111;
    protected $qty = 111;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stock = $this->createMock(\Magento\CatalogInventory\Api\Data\StockInterface::class);
        $this->stockItem = $this->createMock(\Magento\CatalogInventory\Api\Data\StockItemInterface::class);
        $this->stockStatus = $this->createMock(\Magento\CatalogInventory\Api\Data\StockStatusInterface::class);
        $this->objectResult = $this->createMock(\Magento\Framework\DataObject::class);

        $this->stockStateProvider = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface::class,
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
            \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface::class,
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
            \Magento\CatalogInventory\Model\StockState::class,
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
