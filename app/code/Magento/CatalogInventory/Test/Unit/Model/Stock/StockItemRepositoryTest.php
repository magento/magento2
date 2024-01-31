<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\Data as InventoryApiData;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\QueryBuilder;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\DB\QueryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemRepositoryTest extends TestCase
{
    /**
     * @var StockItemRepository
     */
    private $model;

    /**
     * @var Item|MockObject
     */
    private $stockItemMock;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var MockObject
     */
    private $productMock;

    /**
     * @var StockStateProviderInterface|MockObject
     */
    private $stockStateProviderMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item|MockObject
     */
    private $stockItemResourceMock;

    /**
     * @var InventoryApiData\StockItemInterfaceFactory|MockObject
     */
    private $stockItemFactoryMock;

    /**
     * @var InventoryApiData\StockItemCollectionInterfaceFactory|MockObject
     */
    private $stockItemCollectionMock;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var QueryBuilderFactory|MockObject
     */
    private $queryBuilderFactoryMock;

    /**
     * @var MapperFactory|MockObject
     */
    private $mapperMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var Processor|MockObject
     */
    private $indexProcessorMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTime;

    /**
     * @var StockRegistryStorage|MockObject
     */
    private $stockRegistryStorage;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->stockItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setStockStatusChangedAutomaticallyFlag',
                    'getStockStatusChangedAutomaticallyFlag',
                    'hasStockStatusChangedAutomaticallyFlag'
                ]
            )
            ->onlyMethods(
                [
                    'getItemId',
                    'getProductId',
                    'setIsInStock',
                    'getIsInStock',
                    'getStockStatusChangedAuto',
                    'getManageStock',
                    'setLowStockDate',
                    'setStockStatusChangedAuto',
                    'setQty',
                    'getWebsiteId',
                    'setWebsiteId',
                    'getStockId',
                    'setStockId'
                ]
            )
            ->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(
            StockConfigurationInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStateProviderMock = $this->getMockBuilder(
            StockStateProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemResourceMock = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemFactoryMock = $this->getMockBuilder(
            StockItemInterfaceFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemCollectionMock = $this->getMockBuilder(
            StockItemCollectionInterfaceFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['load'])
            ->onlyMethods(['create'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getTypeId', '__wakeup'])
            ->getMock();

        $this->productFactoryMock->expects($this->any())->method('create')->willReturn($this->productMock);

        $this->queryBuilderFactoryMock = $this->getMockBuilder(QueryBuilderFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder(MapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->indexProcessorMock = $this->createPartialMock(
            Processor::class,
            ['reindexRow']
        );
        $this->dateTime = $this->createPartialMock(DateTime::class, ['gmtDate']);
        $this->stockRegistryStorage = $this->getMockBuilder(StockRegistryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->getMock();

        $productCollection->expects($this->any())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->any())->method('addIdFilter')->willReturnSelf();
        $productCollection->expects($this->any())->method('addFieldToSelect')->willReturnSelf();
        $productCollection->expects($this->any())->method('getFirstItem')->willReturn($this->productMock);

        $productCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $productCollectionFactory->expects($this->any())->method('create')->willReturn($productCollection);

        $this->model = (new ObjectManager($this))->getObject(
            StockItemRepository::class,
            [
                'stockConfiguration' => $this->stockConfigurationMock,
                'stockStateProvider' => $this->stockStateProviderMock,
                'resource' => $this->stockItemResourceMock,
                'stockItemFactory' => $this->stockItemFactoryMock,
                'stockItemCollectionFactory' => $this->stockItemCollectionMock,
                'productFactory' => $this->productFactoryMock,
                'queryBuilderFactory' => $this->queryBuilderFactoryMock,
                'mapperFactory' => $this->mapperMock,
                'localeDate' => $this->localeDateMock,
                'indexProcessor' => $this->indexProcessorMock,
                'dateTime' => $this->dateTime,
                'stockRegistryStorage' => $this->stockRegistryStorage,
                'productCollectionFactory' => $productCollectionFactory,
            ]
        );
    }

    public function testDelete()
    {
        $productId = 1;
        $this->stockItemMock->expects($this->atLeastOnce())->method('getProductId')->willReturn($productId);
        $this->stockRegistryStorage->expects($this->once())->method('removeStockItem')->with($productId);
        $this->stockRegistryStorage->expects($this->once())->method('removeStockStatus')->with($productId);

        $this->stockItemResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockItemMock)
            ->willReturnSelf();

        $this->assertTrue($this->model->delete($this->stockItemMock));
    }

    public function testDeleteException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->stockItemResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockItemMock)
            ->willThrowException(new \Exception());

        $this->model->delete($this->stockItemMock);
    }

    public function testDeleteById()
    {
        $id = 1;

        $this->stockItemFactoryMock->expects($this->once())->method('create')->willReturn($this->stockItemMock);
        $this->stockItemResourceMock->expects($this->once())->method('load')->with($this->stockItemMock, $id);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn($id);

        $this->assertTrue($this->model->deleteById($id));
    }

    public function testDeleteByIdException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('The stock item with the "1" ID wasn\'t found. Verify the ID and try again.');
        $id = 1;

        $this->stockItemFactoryMock->expects($this->once())->method('create')->willReturn($this->stockItemMock);
        $this->stockItemResourceMock->expects($this->once())->method('load')->with($this->stockItemMock, $id);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(null);

        $this->assertTrue($this->model->deleteById($id));
    }

    /**
     * @param array $stockStateProviderMockConfig
     * @param array $stockItemMockConfig
     * @param array $existingStockItemMockConfig
     * @return void
     * @throws CouldNotSaveException
     * @dataProvider saveDataProvider
     */
    public function testSave(
        array $stockStateProviderMockConfig,
        array $stockItemMockConfig,
        array $existingStockItemMockConfig
    ) {
        $productId = 1;
        $date = '2023-01-01 00:00:00';
        $stockStateProviderMockConfig += [
            'verifyStock' => ['expects' => $this->once(), 'with' => [$this->stockItemMock], 'willReturn' => true,],
            'verifyNotification' => [
                'expects' => $this->once(),
                'with' => [$this->stockItemMock],
                'willReturn' => true,
            ],
        ];
        $existingStockItemMockConfig += [
            'getItemId' => ['expects' => $this->any(), 'willReturn' => 1,],
            'getIsInStock' => ['expects' => $this->any(), 'willReturn' => false,],
        ];
        $stockItemMockConfig += [
            'getItemId' => ['expects' => $this->any(), 'willReturn' => 1,],
            'getManageStock' => ['expects' => $this->once(), 'willReturn' => true,],
            'getIsInStock' => ['expects' => $this->any(), 'willReturn' => false,],
            'getStockStatusChangedAuto' => ['expects' => $this->once(), 'willReturn' => 1,],
            'getProductId' => ['expects' => $this->once(), 'willReturn' => $productId,],
            'getWebsiteId' => ['expects' => $this->once(), 'willReturn' => 1,],
            'getStockId' => ['expects' => $this->once(), 'willReturn' => 1,],
            'setStockStatusChangedAuto' => ['expects' => $this->never(), 'with' => [1],],
            'setIsInStock' => ['expects' => $this->once(), 'with' => [true],],
            'setWebsiteId' => ['expects' => $this->once(), 'with' => [1], 'willReturnSelf' => true,],
            'setStockId' => ['expects' => $this->once(), 'with' => [1], 'willReturnSelf' => true,],
            'setLowStockDate' => [
                'expects' => $this->exactly(2),
                'willReturnCallback' => [[null], [$date],],
                'willReturnSelf' => true,
            ],
            'hasStockStatusChangedAutomaticallyFlag' => ['expects' => $this->once(), 'willReturn' => false,],

        ];
        $existingStockItem = $this->createMock(Item::class);
        $this->stockItemFactoryMock->expects($this->any())->method('create')->willReturn($existingStockItem);
        $this->configMock($existingStockItem, $existingStockItemMockConfig);
        $this->configMock($this->stockItemMock, $stockItemMockConfig);
        $this->configMock($this->stockStateProviderMock, $stockStateProviderMockConfig);

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('typeId');
        $this->stockConfigurationMock->expects($this->once())
            ->method('isQty')
            ->with('typeId')
            ->willReturn(true);
        $this->dateTime->expects($this->once())
            ->method('gmtDate')
            ->willReturn($date);
        $this->stockItemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockItemMock)
            ->willReturnSelf();

        $this->assertEquals($this->stockItemMock, $this->model->save($this->stockItemMock));
    }

    public function testSaveWithoutProductId()
    {
        $productId = 1;

        $this->stockItemMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getId')->willReturn(null);
        $this->stockRegistryStorage->expects($this->never())->method('removeStockItem');
        $this->stockRegistryStorage->expects($this->never())->method('removeStockStatus');

        $this->assertEquals($this->stockItemMock, $this->model->save($this->stockItemMock));
    }

    public function testSaveException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $productId = 1;

        $this->stockItemMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('typeId');
        $this->stockConfigurationMock->expects($this->once())->method('isQty')->with('typeId')->willReturn(false);
        $this->stockItemMock->expects($this->once())->method('setQty')->with(0)->willReturnSelf();
        $this->stockItemMock->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('setWebsiteId')->with(1)->willReturnSelf();
        $this->stockItemMock->expects($this->once())->method('getStockId')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('setStockId')->with(1)->willReturnSelf();
        $this->stockItemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockItemMock)
            ->willThrowException(new \Exception());

        $this->model->save($this->stockItemMock);
    }

    public function testGetList()
    {
        $criteriaMock = $this->getMockBuilder(StockItemCriteriaInterface::class)
            ->getMock();
        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setCriteria', 'setResource', 'create'])
            ->getMock();
        $queryMock = $this->getMockBuilder(QueryInterface::class)
            ->getMock();
        $queryCollectionMock = $this->getMockBuilder(
            StockItemCollectionInterface::class
        )->getMock();

        $this->queryBuilderFactoryMock->expects($this->once())->method('create')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setCriteria')->with($criteriaMock)->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setResource')
            ->with($this->stockItemResourceMock)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('create')->willReturn($queryMock);
        $this->stockItemCollectionMock->expects($this->once())->method('create')->willReturn($queryCollectionMock);

        $this->assertEquals($queryCollectionMock, $this->model->getList($criteriaMock));
    }

    /**
     * @return array
     */
    public static function saveDataProvider(): array
    {
        return [
            'should set isInStock=true if: verifyStock=true, isInStock=false, stockStatusChangedAuto=true' => [
                'stockStateProviderMockConfig' => [],
                'stockItemMockConfig' => [],
                'existingStockItemMockConfig' => [],
            ],
            'should not set isInStock=true if: verifyStock=true, isInStock=false, stockStatusChangedAuto=false' => [
                'stockStateProviderMockConfig' => [],
                'stockItemMockConfig' => [
                    'setIsInStock' => ['expects' => self::never(),],
                    'setStockStatusChangedAuto' => ['expects' => self::never()],
                    'getStockStatusChangedAuto' => ['expects' => self::once(), 'willReturn' => false,],
                ],
                'existingStockItemMockConfig' => [],
            ],
            'should set isInStock=false and stockStatusChangedAuto=true if: verifyStock=false and isInStock=true' => [
                'stockStateProviderMockConfig' => [
                    'verifyStock' => ['expects' => self::once(), 'willReturn' => false,],
                ],
                'stockItemMockConfig' => [
                    'getIsInStock' => ['expects' => self::any(), 'willReturn' => true,],
                    'getStockStatusChangedAuto' => ['expects' => self::never(),],
                    'setIsInStock' => ['expects' => self::once(), 'with' => [false],],
                    'setStockStatusChangedAuto' => ['expects' => self::once(), 'with' => [1],],
                ],
                'existingStockItemMockConfig' => [],
            ],
            'should set stockStatusChangedAuto=true if: verifyStock=false and isInStock=false' => [
                'stockStateProviderMockConfig' => [
                    'verifyStock' => ['expects' => self::once(), 'willReturn' => false,],
                ],
                'stockItemMockConfig' => [
                    'getIsInStock' => ['expects' => self::any(), 'willReturn' => false,],
                    'getStockStatusChangedAuto' => ['expects' => self::never(),],
                    'setIsInStock' => ['expects' => self::never(),],
                    'setStockStatusChangedAuto' => ['expects' => self::never(),],
                ],
                'existingStockItemMockConfig' => [],
            ],
            'should set stockStatusChangedAuto=true if: stockStatusChangedAutomaticallyFlag=true' => [
                'stockStateProviderMockConfig' => [],
                'stockItemMockConfig' => [
                    'getStockStatusChangedAuto' => ['expects' =>self::once(), 'willReturn' => false,],
                    'setIsInStock' => ['expects' => self::never(),],
                    'setStockStatusChangedAuto' => ['expects' => self::once(), 'with' => [1],],
                    'hasStockStatusChangedAutomaticallyFlag' => ['expects' => self::once(), 'willReturn' => true,],
                    'getStockStatusChangedAutomaticallyFlag' => ['expects' => self::once(), 'willReturn' => true,],
                ],
                'existingStockItemMockConfig' => [
                ],
            ],
            'should set stockStatusChangedAuto=false if: getManageStock=false' => [
                'stockStateProviderMockConfig' => [],
                'stockItemMockConfig' => [
                    'getManageStock' => ['expects' => self::once(), 'willReturn' => false],
                    'getStockStatusChangedAuto' => ['expects' => self::never(), 'willReturn' => false,],
                    'setIsInStock' => ['expects' => self::never(),],
                    'setStockStatusChangedAuto' => ['expects' => self::once(), 'with' => [0],],
                ],
                'existingStockItemMockConfig' => [
                ],
            ]
        ];
    }

    /**
     * @param MockObject $mockObject
     * @param array $configs
     * @return void
     */
    private function configMock(MockObject $mockObject, array $configs): void
    {
        foreach ($configs as $method => $config) {
            $mockMethod = $mockObject->expects($config['expects'])->method($method);
            if (isset($config['with'])) {
                $mockMethod->with(...$config['with']);
            }
            if (isset($config['willReturnCallback'])) {
                $mockMethod->willReturnCallback(...$config['willReturnCallback']);
            }
            if (isset($config['willReturnSelf'])) {
                $mockMethod->willReturnSelf();
            }
            if (isset($config['willReturn'])) {
                $mockMethod->willReturn($config['willReturn']);
            }
        }
    }
}
