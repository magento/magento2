<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\ResourceModel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\AdvancedSearch\Model\ResourceModel\Index;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\MultiDimensionProvider;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Traversable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[CoversClass(Index::class)]
class IndexTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var Index
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Context|MockObject
     */
    private $resourceContextMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->resourceContextMock = $this->createMock(Context::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->resourceContextMock->method('getResources')->willReturn($this->resourceConnectionMock);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->method('getConnection')->willReturn($this->adapterMock);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        /** @var IndexScopeResolver|MockObject $indexScopeResolverMock */
        $indexScopeResolverMock = $this->createMock(IndexScopeResolver::class);

        /** @var Traversable|MockObject $traversableMock */
        $traversableMock = $this->createMock(Traversable::class);

        /** @var MultiDimensionProvider|MockObject $dimensionsMock */
        $dimensionsMock = $this->createMock(MultiDimensionProvider::class);
        $dimensionsMock->method('getIterator')->willReturn($traversableMock);

        /** @var DimensionCollectionFactory|MockObject $dimensionFactoryMock */
        $dimensionFactoryMock = $this->createMock(DimensionCollectionFactory::class);
        $dimensionFactoryMock->method('create')->willReturn($dimensionsMock);

        $this->model = new Index(
            $this->resourceContextMock,
            $this->storeManagerMock,
            $this->metadataPoolMock,
            'connectionName',
            $indexScopeResolverMock,
            $dimensionFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testGetPriceIndexDataUsesFrontendPriceIndexerTable(): void
    {
        $storeId = 1;
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)->willReturn($storeMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('union')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchAll')->with($selectMock)->willReturn([]);

        $this->assertEmpty($this->model->getPriceIndexData([1], $storeId));
    }

    /**
     * @param array $testData
     *
     * @return void
     */
    #[DataProvider('providerForTestPriceIndexData')]
    public function testGetPriceIndexData(array $testData): void
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn($testData['website_id']);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with(1)->willReturn($storeMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('union')->willReturnSelf();
        $this->adapterMock->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->any())->method('fetchAll')->with($selectMock)->willReturn([$testData]);
        $expectedData = [
            $testData['entity_id'] => [
                $testData['customer_group_id'] => round((float) $testData['min_price'], 2)
            ]
        ];

        $this->assertEquals($this->model->getPriceIndexData([1], 1), $expectedData);
    }

    /**
     * @return array
     */
    public static function providerForTestPriceIndexData(): array
    {
        return [
            [
               [
                   'website_id' => 1,
                   'entity_id' => 1,
                   'customer_group_id' => 1,
                   'min_price' => '12.12'
               ]
            ],
            [
                [
                    'website_id' => 1,
                    'entity_id' => 2,
                    'customer_group_id' => 2,
                    'min_price' => null
                ]
            ],
            [
                [
                    'website_id' => 1,
                    'entity_id' => 3,
                    'customer_group_id' => 3,
                    'min_price' => 12.12
                ]
            ],
            [
                [
                    'website_id' => 1,
                    'entity_id' => 3,
                    'customer_group_id' => 3,
                    'min_price' => ''
                ]
            ]
        ];
    }
}
