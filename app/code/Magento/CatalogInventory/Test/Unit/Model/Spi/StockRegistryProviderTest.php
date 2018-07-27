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
class StockRegistryProviderTest extends \PHPUnit_Framework_TestCase
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
            'Magento\CatalogInventory\Api\Data\StockInterface',
            ['__wakeup', 'getStockId'],
            '',
            false
        );
        $this->stockItem = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
            ['__wakeup', 'getItemId'],
            '',
            false
        );
        $this->stockStatus = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockStatusInterface',
            ['__wakeup', 'getProductId'],
            '',
            false
        );

        $this->stockFactory = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockFactory->expects($this->any())->method('create')->willReturn($this->stock);

        $this->stockItemFactory = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockItemFactory->expects($this->any())->method('create')->willReturn($this->stockItem);

        $this->stockStatusFactory = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockStatusFactory->expects($this->any())->method('create')->willReturn($this->stockStatus);

        $this->stockRepository = $this->getMockBuilder('\Magento\CatalogInventory\Api\StockRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemRepository = $this->getMockBuilder('\Magento\CatalogInventory\Api\StockItemRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStatusRepository = $this->getMockBuilder(
            '\Magento\CatalogInventory\Api\StockStatusRepositoryInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockCriteriaFactory = $this->getMock(
            'Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockCriteria = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockCriteriaInterface',
            ['setScopeFilter'],
            '',
            false
        );

        $this->stockItemCriteriaFactory = $this->getMock(
            'Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockItemCriteria = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockItemCriteriaInterface',
            ['setProductsFilter', 'setScopeFilter'],
            '',
            false
        );

        $this->stockStatusCriteriaFactory = $this->getMock(
            'Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockStatusCriteria = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockStatusCriteriaInterface',
            ['setProductsFilter', 'setScopeFilter'],
            '',
            false
        );

        $this->stockRegistryProvider = $this->objectManagerHelper->getObject(
            '\Magento\CatalogInventory\Model\StockRegistryProvider',
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
                'stockRegistryStorage' => $this->getMock('Magento\CatalogInventory\Model\StockRegistryStorage')
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
        $stockCollection = $this->getMock(
            '\Magento\CatalogInventory\Model\ResourceModel\Stock\Collection',
            ['getFirstItem', '__wakeup', 'getItems'],
            [],
            '',
            false
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
        $stockItemCollection = $this->getMock(
            '\Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection',
            ['getFirstItem', '__wakeup', 'getItems'],
            [],
            '',
            false
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
        $stockStatusCollection = $this->getMock(
            '\Magento\CatalogInventory\Model\ResourceModel\Stock\Status\Collection',
            ['getFirstItem', '__wakeup', 'getItems'],
            [],
            '',
            false
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
