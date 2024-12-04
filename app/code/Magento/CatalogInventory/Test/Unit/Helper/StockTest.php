<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockTest extends TestCase
{
    /**
     * @var Stock
     */
    protected $stock;

    /**
     * @var MockObject|StockRegistryProviderInterface
     */
    protected $stockRegistryProviderMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject|StatusFactory
     */
    protected $statusFactoryMock;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    protected $stockConfiguration;

    protected function setUp(): void
    {
        $this->stockRegistryProviderMock = $this->getMockBuilder(
            StockRegistryProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->statusFactoryMock =
            $this->getMockBuilder(StatusFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->stockConfiguration = $this->getMockBuilder(
            StockConfigurationInterface::class
        )->getMock();
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

        $stockStatusMock = $this->getMockBuilder(StockStatusInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $stockStatusMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($status);
        $this->stockRegistryProviderMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($stockStatusMock);
        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn($websiteId);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsSalable'])
            ->onlyMethods(['getId'])
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

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsSalable'])
            ->onlyMethods(['getId'])
            ->getMock();
        $productMock->expects($this->once())
            ->method('setIsSalable')
            ->with($status);
        $stockStatusMock = $this->getMockBuilder(StockStatusInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn($status);
        $productCollectionMock =
            $this->getMockBuilder(AbstractCollection::class)
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
        if ($configMock!=null) {
            $configMock = $configMock($this);
        }

        $collectionMock = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('joinField');
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn($configMock);
        $this->assertNull($this->stock->addInStockFilterToCollection($collectionMock));
    }

    public function getMockForConfigClass()
    {
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $configMock;
    }

    /**
     * @return array
     */
    public static function filterProvider()
    {
        $configMock = static fn (self $testCase) => $testCase->getMockForConfigClass();
        return [
            [$configMock],
            [null],
        ];
    }

    public function testAddIsInStockFilterToCollection()
    {
        $collectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusMock = $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addStockDataToCollection'])
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
