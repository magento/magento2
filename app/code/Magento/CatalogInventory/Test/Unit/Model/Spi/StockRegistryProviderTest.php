<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Spi;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StockRegistryProviderTest
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
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemCriteria;

    /**
     * @var \Magento\CatalogInventory\Api\StockStatusCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusCriteria;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfiguration;

    /**
     * @var int
     */
    protected $stockId = 111;

    /**
     * @var int
     */
    protected $productId = 112;

    /**
     * @var string
     */
    protected $productSku = 'simple';

    /**
     * @var int
     */
    protected $scopeId = 113;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stockFactory = $this->getMockBuilder('\Magento\CatalogInventory\Api\Data\StockInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockItemFactory = $this->getMockBuilder('\Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockStatusFactory = $this->getMockBuilder(
            '\Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockStatusFactory->expects($this->any())->method('create')->willReturn($this->stockStatus);
        $this->stockCriteriaFactory = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockItemCriteriaFactory = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockStatusCriteriaFactory = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

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
        $this->stockConfiguration = $this->getMockBuilder('Magento\CatalogInventory\Api\StockConfigurationInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultScopeId'])
            ->getMockForAbstractClass();

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
                'stockConfiguration' => $this->stockConfiguration
            ]
        );
    }

    protected function tearDown()
    {
        $this->stockRegistryProvider = null;
    }

    public function testGetStockWithStock()
    {
        $stock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockId'])
            ->getMockForAbstractClass();
        $this->stockFactory->expects($this->any())->method('create')->willReturn($stock);
        $stock->expects($this->once())->method('getStockId')->willReturn($this->stockId);
        $this->stockRepository->expects($this->once())->method('get')->willReturn($stock);
        $this->assertEquals($stock, $this->stockRegistryProvider->getStock($this->stockId));
    }

    public function testGetStockWithoutStock()
    {
        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn($this->scopeId);
        $stock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockId'])
            ->getMockForAbstractClass();
        $stock->expects($this->once())->method('getStockId')->willReturn($this->stockId);
        $stockCollection = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockCollectionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $stockCriteria = $this->getMockBuilder('\Magento\CatalogInventory\Api\StockCriteriaInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $stockCriteria->expects($this->once())->method('setScopeFilter')->with($this->scopeId)->willReturnSelf();
        $this->stockCriteriaFactory->expects($this->once())->method('create')->willReturn($stockCriteria);
        $this->stockRepository->expects($this->once())->method('getList')
            ->with($stockCriteria)->willReturn($stockCollection);
        $stockCollection->expects($this->once())->method('getItems')->willReturn([$stock]);
        $this->assertEquals($stock, $this->stockRegistryProvider->getStock());
    }

    public function testGetStockItem()
    {
        $stock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockId'])
            ->getMockForAbstractClass();
        $this->stockRepository->expects($this->once())->method('get')->willReturn($stock);
        $stockItemCriteria = $this->getMockBuilder('Magento\CatalogInventory\Api\StockItemCriteriaInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setProductsFilter', 'setStockFilter'])
            ->getMockForAbstractClass();
        $this->stockItemCriteriaFactory->expects($this->once())->method('create')->willReturn($stockItemCriteria);
        $stockItemCriteria->expects($this->once())->method('setProductsFilter')->with($this->productId)
            ->willReturnSelf();
        $stock->expects($this->once())->method('getStockId')->willReturn($this->stockId);
        $stockItemCriteria->expects($this->once())->method('setStockFilter')->with($stock)
            ->willReturnSelf();
        $stockItemCollection = $this->getMockBuilder(
            '\Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection'
        )
            ->disableOriginalConstructor()
            ->setMethods(['getFirstItem', 'getItems'])
            ->getMock();
        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getItemId'])
            ->getMockForAbstractClass();
        $stockItemCollection->expects($this->once())->method('getItems')->willReturn([$stockItem]);
        $this->stockItemRepository->expects($this->once())->method('getList')->willReturn($stockItemCollection);
        $stockItem->expects($this->once())->method('getItemId')->willReturn(true);
        $this->assertEquals(
            $stockItem,
            $this->stockRegistryProvider->getStockItem($this->productId, $this->stockId)
        );
    }

    public function testGetStockStatus()
    {
        $stock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockId'])
            ->getMockForAbstractClass();
        $this->stockFactory->expects($this->any())->method('create')->willReturn($stock);
        $stock->expects($this->once())->method('getStockId')->willReturn($this->stockId);
        $this->stockRepository->expects($this->once())->method('get')->willReturn($stock);
        $stockStatusCriteria = $this->getMockBuilder('Magento\CatalogInventory\Api\StockStatusCriteriaInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setProductsFilter', 'setStockFilter'])
            ->getMockForAbstractClass();
        $this->stockStatusCriteriaFactory->expects($this->once())
            ->method('create')
            ->willReturn($stockStatusCriteria);
        $stockStatusCriteria->expects($this->once())->method('setProductsFilter')->with($this->productId)
            ->willReturnSelf();
        $stockStatusCriteria->expects($this->once())->method('setStockFilter')->with($stock)
            ->willReturnSelf();
        $stockStatusCollection = $this->getMockBuilder(
            '\Magento\CatalogInventory\Model\ResourceModel\Stock\Status\Collection'
        )
            ->disableOriginalConstructor()
            ->setMethods(['getFirstItem', 'getItems'])
            ->getMock();
        $stockStatus = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockStatusInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getProductId'])
            ->getMockForAbstractClass();
        $stockStatusCollection->expects($this->once())->method('getItems')->willReturn([$stockStatus]);
        $stockStatus->expects($this->once())->method('getProductId')->willReturn($this->productId);
        $this->stockStatusRepository->expects($this->once())->method('getList')->willReturn($stockStatusCollection);
        $this->assertEquals(
            $stockStatus,
            $this->stockRegistryProvider->getStockStatus($this->productId, $this->stockId)
        );
    }
}
