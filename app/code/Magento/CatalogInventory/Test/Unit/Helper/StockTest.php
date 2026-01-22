<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class StockTest extends TestCase
{
    use MockCreationTrait;

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
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->getMock();
        $stockStatusMock->method('getStockStatus')->willReturn($status);
        $this->stockRegistryProviderMock->method('getStockStatus')->willReturn($stockStatusMock);
        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn($websiteId);

        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getId', 'setIsSalable']
        );
        
        $productMock->setIsSalable($status);
        $this->assertNull($this->stock->assignStatusToProduct($productMock));
    }

    public function testAddStockStatusToProducts()
    {
        $storeId = 1;
        $productId = 2;
        $status = 'test';

        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getId', 'setIsSalable']
        );
        
        $productMock->setIsSalable($status);
        $productMock->setId($productId);
        
        $stockStatusMock = $this->createMock(StockStatusInterface::class);
        $stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn($status);
        $productCollectionMock = $this->createMock(AbstractCollection::class);
        $productCollectionMock->expects($this->any())
            ->method('getItemById')
            ->with($productId)
            ->willReturn($productMock);
        $productCollectionMock->method('getStoreId')->willReturn($storeId);
        $iteratorMock = new \ArrayIterator([$productMock]);

        $productCollectionMock->method('getIterator')->willReturn($iteratorMock);
        $this->stockRegistryProviderMock->expects($this->once())
            ->method('getStockStatus')
            ->withAnyParameters()
            ->willReturn($stockStatusMock);

        $this->assertNull($this->stock->addStockStatusToProducts($productCollectionMock));
    }

    #[DataProvider('filterProvider')]
    public function testAddInStockFilterToCollection($configMock)
    {
        if ($configMock!=null) {
            $configMock = $configMock($this);
        }

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->any())
            ->method('joinField');
        $this->scopeConfigMock->method('getValue')->willReturn($configMock);
        $this->assertNull($this->stock->addInStockFilterToCollection($collectionMock));
    }

    public function getMockForConfigClass()
    {
        $configMock = $this->createMock(Config::class);
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
        $collectionMock = $this->createMock(ProductCollection::class);
        $stockStatusMock = $this->createMock(Status::class);
        $stockStatusMock->expects($this->once())
            ->method('addStockDataToCollection')
            ->with($collectionMock);
        $this->statusFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($stockStatusMock);

        $this->assertNull($this->stock->addIsInStockFilterToCollection($collectionMock));
    }
}
