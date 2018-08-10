<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Helper;

use \Magento\CatalogInventory\Helper\Stock;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    protected $stockRegistryProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory
     */
    protected $statusFactoryMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfiguration;

    protected function setUp()
    {
        $this->stockRegistryProviderMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusFactoryMock =
            $this->getMockBuilder('Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockConfiguration = $this->getMockBuilder('Magento\CatalogInventory\Api\StockConfigurationInterface')
            ->getMock();
        $this->stock = new Stock(
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->statusFactoryMock,
            $this->stockRegistryProviderMock
        );

        // Todo: \Magento\Framework\TestFramework\Unit\Helper\ObjectManager to do this automatically (MAGETWO-49793)
        $reflection = new \ReflectionClass(get_class($this->stock));
        $reflectionProperty = $reflection->getProperty('stockConfiguration');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->stock, $this->stockConfiguration);
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
        $this->stockRegistryProviderMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($stockStatusMock);
        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn($websiteId);

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['setIsSalable', 'getId'])
            ->getMock();
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
        $productCollectionMock =
            $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection')
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
        $this->stockRegistryProviderMock->expects($this->once())
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
        $collectionMock = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('joinField');
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn($configMock);
        $this->assertNull($this->stock->addInStockFilterToCollection($collectionMock));
    }

    /**
     * @return array
     */
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

    public function testAddIsInStockFilterToCollection()
    {
        $collectionMock = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusMock = $this->getMockBuilder('Magento\CatalogInventory\Model\ResourceModel\Stock\Status')
            ->disableOriginalConstructor()
            ->setMethods(['addStockDataToCollection'])
            ->getMock();
        $stockStatusMock->expects($this->once())
            ->method('addStockDataToCollection')
            ->with($collectionMock);
        $this->statusFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($stockStatusMock);

        $this->assertNull($this->stock->addIsInStockFilterToCollection($collectionMock));
    }
}
