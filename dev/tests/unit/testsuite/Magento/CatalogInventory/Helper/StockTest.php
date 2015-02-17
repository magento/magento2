<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Helper;

/**
 * Class StockTest
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->stockRegistryMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\StockRegistryInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\App\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stock = new Stock(
            $this->stockRegistryMock,
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->objectManagerMock
        );
    }

    public function testAssignStatusToProduct()
    {
        $websiteId = 1;
        $status = 'test';

        $stockStatusMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockStatusInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($status);
        $this->stockRegistryMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($stockStatusMock);
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['setIsSalable', 'getStore', 'getId'])
            ->getMock();
        $productMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $productMock->expects($this->once())
            ->method('setIsSalable')
            ->with($status);
        $this->assertNull($this->stock->assignStatusToProduct($productMock));
    }

    public function testAddStockStatusToProducts()
    {
        $storeId = 1;
        $productId = 2;
        $status = 'test';

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['setIsSalable', 'getId'])
            ->getMock();
        $productMock->expects($this->once())
            ->method('setIsSalable')
            ->with($status);
        $stockStatusMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockStatusInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn($status);
        $productCollectionMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Collection\AbstractCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionMock->expects($this->any())
            ->method('getItemById')
            ->with($productId)
            ->willReturn($productMock);
        $productCollectionMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $iteratorMock = new \ArrayIterator([$productMock]);

        $productCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn($iteratorMock);
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockStatus')
            ->withAnyParameters()
            ->willReturn($stockStatusMock);

        $this->assertNull($this->stock->addStockStatusToProducts($productCollectionMock));
    }

    /**
     * @dataProvider filterProvider
     */
    public function testAddInStockFilterToCollection($configMock)
    {
        $collectionMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Link\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('joinField');
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn($configMock);
        $this->assertNull($this->stock->addInStockFilterToCollection($collectionMock));
    }

    public function filterProvider()
    {
        $configMock = $this->getMockBuilder('Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->getMock();
        return [
            [$configMock],
            [null],
        ];
    }

    public function testAddStockStatusToSelect()
    {
        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusMock = $this->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Status')
            ->disableOriginalConstructor()
            ->setMethods(['addStockStatusToSelect'])
            ->getMock();
        $stockStatusMock->expects($this->once())
            ->method('addStockStatusToSelect')
            ->with($selectMock, $websiteMock);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($stockStatusMock);

        $this->assertNull($this->stock->addStockStatusToSelect($selectMock, $websiteMock));
    }

    public function testAddIsInStockFilterToCollection()
    {
        $collectionMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusMock = $this->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Status')
            ->disableOriginalConstructor()
            ->setMethods(['addIsInStockFilterToCollection'])
            ->getMock();
        $stockStatusMock->expects($this->once())
            ->method('addIsInStockFilterToCollection')
            ->with($collectionMock);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($stockStatusMock);

        $this->assertNull($this->stock->addIsInStockFilterToCollection($collectionMock));
    }
}
