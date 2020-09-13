<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Spi;

use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\Data\StockInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Api\StockCriteriaInterface;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Collection;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\StockRegistryProvider;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StockRegistryProviderTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

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
     * @var StockStatusInterfaceFactory|MockObject
     */
    protected $stockStatusFactory;

    /**
     * @var StockItemInterfaceFactory|MockObject
     */
    protected $stockItemFactory;

    /**
     * @var StockInterfaceFactory|MockObject
     */
    protected $stockFactory;

    /**
     * @var StockRepositoryInterface|MockObject
     */
    protected $stockRepository;

    /**
     * @var StockItemRepositoryInterface|MockObject
     */
    protected $stockItemRepository;

    /**
     * @var StockStatusRepositoryInterface|MockObject
     */
    protected $stockStatusRepository;

    /**
     * @var StockCriteriaInterfaceFactory|MockObject
     */
    protected $stockCriteriaFactory;

    /**
     * @var StockItemCriteriaInterfaceFactory|MockObject
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var StockStatusCriteriaInterfaceFactory|MockObject
     */
    protected $stockStatusCriteriaFactory;

    /**
     * @var StockCriteriaInterface|MockObject
     */
    protected $stockCriteria;

    /**
     * @var StockItemCriteriaInterface|MockObject
     */
    protected $stockItemCriteria;

    /**
     * @var StockStatusCriteriaInterface|MockObject
     */
    protected $stockStatusCriteria;

    protected $productId = 111;
    protected $productSku = 'simple';
    protected $scopeId = 111;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stock = $this->getMockForAbstractClass(
            StockInterface::class,
            ['getStockId'],
            '',
            false
        );
        $this->stockItem = $this->getMockForAbstractClass(
            StockItemInterface::class,
            ['getItemId'],
            '',
            false
        );
        $this->stockStatus = $this->getMockForAbstractClass(
            StockStatusInterface::class,
            ['getProductId'],
            '',
            false
        );

        $this->stockFactory = $this->createPartialMock(
            StockInterfaceFactory::class,
            ['create']
        );
        $this->stockFactory->expects($this->any())->method('create')->willReturn($this->stock);

        $this->stockItemFactory = $this->createPartialMock(
            StockItemInterfaceFactory::class,
            ['create']
        );
        $this->stockItemFactory->expects($this->any())->method('create')->willReturn($this->stockItem);

        $this->stockStatusFactory = $this->createPartialMock(
            StockStatusInterfaceFactory::class,
            ['create']
        );
        $this->stockStatusFactory->expects($this->any())->method('create')->willReturn($this->stockStatus);

        $this->stockRepository = $this->getMockBuilder(StockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockItemRepository = $this->getMockBuilder(
            StockItemRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStatusRepository = $this->getMockBuilder(
            StockStatusRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockCriteriaFactory = $this->createPartialMock(
            StockCriteriaInterfaceFactory::class,
            ['create']
        );
        $this->stockCriteria = $this->getMockForAbstractClass(
            StockCriteriaInterface::class,
            ['setScopeFilter'],
            '',
            false
        );

        $this->stockItemCriteriaFactory = $this->createPartialMock(
            StockItemCriteriaInterfaceFactory::class,
            ['create']
        );
        $this->stockItemCriteria = $this->getMockForAbstractClass(
            StockItemCriteriaInterface::class,
            ['setProductsFilter', 'setScopeFilter'],
            '',
            false
        );

        $this->stockStatusCriteriaFactory = $this->createPartialMock(
            StockStatusCriteriaInterfaceFactory::class,
            ['create']
        );
        $this->stockStatusCriteria = $this->getMockForAbstractClass(
            StockStatusCriteriaInterface::class,
            ['setProductsFilter', 'setScopeFilter'],
            '',
            false
        );

        $this->stockRegistryProvider = $this->objectManagerHelper->getObject(
            StockRegistryProvider::class,
            [
                'stockRepository' => $this->stockRepository,
                'stockFactory' => $this->stockFactory,
                'stockItemRepository' => $this->stockItemRepository,
                'stockItemFactory' => $this->stockItemFactory,
                'stockStatusRepository' => $this->stockStatusRepository,
                'stockStatusFactory' => $this->stockStatusFactory,
                'stockCriteriaFactory' => $this->stockCriteriaFactory,
                'stockItemCriteriaFactory' => $this->stockItemCriteriaFactory,
                'stockStatusCriteriaFactory' => $this->stockStatusCriteriaFactory,
                'stockRegistryStorage' => $this->createMock(StockRegistryStorage::class)
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->stockRegistryProvider = null;
    }

    public function testGetStock()
    {
        $this->stockCriteriaFactory->expects($this->once())->method('create')->willReturn($this->stockCriteria);
        $this->stockCriteria->expects($this->once())->method('setScopeFilter')->willReturn(null);
        $stockCollection = $this->getMockBuilder(Collection::class)
            ->addMethods(['getFirstItem'])
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $stockCollection->expects($this->once())->method('getItems')->willReturn([$this->stock]);
        $this->stockRepository->expects($this->once())->method('getList')->willReturn($stockCollection);
        $this->stock->expects($this->once())->method('getStockId')->willReturn(true);
        $this->assertEquals($this->stock, $this->stockRegistryProvider->getStock($this->scopeId));
    }

    public function testGetStockItem()
    {
        $this->stockItemCriteriaFactory->expects($this->once())->method('create')->willReturn($this->stockItemCriteria);
        $this->stockItemCriteria->expects($this->once())->method('setProductsFilter')->willReturn(null);
        $stockItemCollection = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection::class
        )->addMethods(['getFirstItem'])
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $stockItemCollection->expects($this->once())->method('getItems')->willReturn([$this->stockItem]);
        $this->stockItemRepository->expects($this->once())->method('getList')->willReturn($stockItemCollection);
        $this->stockItem->expects($this->once())->method('getItemId')->willReturn(true);
        $this->assertEquals(
            $this->stockItem,
            $this->stockRegistryProvider->getStockItem($this->productId, $this->scopeId)
        );
    }

    public function testGetStockStatus()
    {
        $this->stockStatusCriteriaFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->stockStatusCriteria);
        $this->stockStatusCriteria->expects($this->once())->method('setScopeFilter')->willReturn(null);
        $this->stockStatusCriteria->expects($this->once())->method('setProductsFilter')->willReturn(null);
        $stockStatusCollection = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Status\Collection::class
        )->addMethods(['getFirstItem'])
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusCollection->expects($this->once())->method('getItems')->willReturn([$this->stockStatus]);
        $this->stockStatusRepository->expects($this->once())->method('getList')->willReturn($stockStatusCollection);
        $this->stockStatus->expects($this->once())->method('getProductId')->willReturn($this->productId);
        $this->assertEquals(
            $this->stockStatus,
            $this->stockRegistryProvider->getStockStatus($this->productId, $this->scopeId)
        );
    }
}
