<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Spi;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StockRegistryProviderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StockRegistryProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatus;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusFactory;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemFactory;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockStatusRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockCriteriaFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusCriteriaFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockCriteria;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemCriteria;

    /**
     * @var \Magento\CatalogInventory\Api\StockStatusCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusCriteria;

    protected $productId = 111;
    protected $productSku = 'simple';
    protected $scopeId = 111;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockInterface::class,
            ['__wakeup', 'getStockId'],
            '',
            false
        );
        $this->stockItem = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class,
            ['__wakeup', 'getItemId'],
            '',
            false
        );
        $this->stockStatus = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockStatusInterface::class,
            ['__wakeup', 'getProductId'],
            '',
            false
        );

        $this->stockFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Api\Data\StockInterfaceFactory::class,
            ['create']
        );
        $this->stockFactory->expects($this->any())->method('create')->willReturn($this->stock);

        $this->stockItemFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class,
            ['create']
        );
        $this->stockItemFactory->expects($this->any())->method('create')->willReturn($this->stockItem);

        $this->stockStatusFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory::class,
            ['create']
        );
        $this->stockStatusFactory->expects($this->any())->method('create')->willReturn($this->stockStatus);

        $this->stockRepository = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemRepository = $this->getMockBuilder(
            \Magento\CatalogInventory\Api\StockItemRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStatusRepository = $this->getMockBuilder(
            \Magento\CatalogInventory\Api\StockStatusRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockCriteriaFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory::class,
            ['create']
        );
        $this->stockCriteria = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockCriteriaInterface::class,
            ['setScopeFilter'],
            '',
            false
        );

        $this->stockItemCriteriaFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory::class,
            ['create']
        );
        $this->stockItemCriteria = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockItemCriteriaInterface::class,
            ['setProductsFilter', 'setScopeFilter'],
            '',
            false
        );

        $this->stockStatusCriteriaFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory::class,
            ['create']
        );
        $this->stockStatusCriteria = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockStatusCriteriaInterface::class,
            ['setProductsFilter', 'setScopeFilter'],
            '',
            false
        );

        $this->stockRegistryProvider = $this->objectManagerHelper->getObject(
            \Magento\CatalogInventory\Model\StockRegistryProvider::class,
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
                'stockRegistryStorage' => $this->createMock(\Magento\CatalogInventory\Model\StockRegistryStorage::class)
            ]
        );
    }

    protected function tearDown()
    {
        $this->stockRegistryProvider = null;
    }

    public function testGetStock()
    {
        $this->stockCriteriaFactory->expects($this->once())->method('create')->willReturn($this->stockCriteria);
        $this->stockCriteria->expects($this->once())->method('setScopeFilter')->willReturn(null);
        $stockCollection = $this->createPartialMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Collection::class,
            ['getFirstItem', '__wakeup', 'getItems']
        );
        $stockCollection->expects($this->once())->method('getItems')->willReturn([$this->stock]);
        $this->stockRepository->expects($this->once())->method('getList')->willReturn($stockCollection);
        $this->stock->expects($this->once())->method('getStockId')->willReturn(true);
        $this->assertEquals($this->stock, $this->stockRegistryProvider->getStock($this->scopeId));
    }

    public function testGetStockItem()
    {
        $this->stockItemCriteriaFactory->expects($this->once())->method('create')->willReturn($this->stockItemCriteria);
        $this->stockItemCriteria->expects($this->once())->method('setProductsFilter')->willReturn(null);
        $stockItemCollection = $this->createPartialMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection::class,
            ['getFirstItem', '__wakeup', 'getItems']
        );
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
        $stockStatusCollection = $this->createPartialMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Status\Collection::class,
            ['getFirstItem', '__wakeup', 'getItems']
        );
        $stockStatusCollection->expects($this->once())->method('getItems')->willReturn([$this->stockStatus]);
        $this->stockStatusRepository->expects($this->once())->method('getList')->willReturn($stockStatusCollection);
        $this->stockStatus->expects($this->once())->method('getProductId')->willReturn($this->productId);
        $this->assertEquals(
            $this->stockStatus,
            $this->stockRegistryProvider->getStockStatus($this->productId, $this->scopeId)
        );
    }
}
