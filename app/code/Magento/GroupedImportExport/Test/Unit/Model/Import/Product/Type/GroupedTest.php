<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedImportExport;
use Magento\GroupedImportExport\Model\Import\Product\Type\Grouped;
use Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupedTest extends AbstractImportTestCase
{
    use MockCreationTrait;
    /**
     * @var GroupedImportExport\Model\Import\Product\Type\Grouped
     */
    private $grouped;

    /**
     * @var AttributeSetCollectionFactory|MockObject
     */
    private $setCollectionFactory;

    /**
     * @var ProductAttributeCollectionFactory|MockObject
     */
    private $attrCollectionFactory;

    /**
     * @var ProductAttributeCollection|MockObject
     */
    private $attrCollection;

    /**
     * @var Mysql|MockObject
     */
    private $connection;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var []
     */
    private $params;

    /**
     * @var GroupedImportExport\Model\Import\Product\Type\Grouped\Links|MockObject
     */
    private $links;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var Product|MockObject
     */
    private $entityModel;

    /**
     * @var Product\SkuStorage|MockObject
     */
    private Product\SkuStorage $skuStorage;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setCollectionFactory = $this->createMock(AttributeSetCollectionFactory::class);
        $this->attrCollectionFactory = $this->createMock(ProductAttributeCollectionFactory::class);
        $this->attrCollection = $this->createMock(ProductAttributeCollection::class);
        $this->attrCollectionFactory->method('create')->willReturn($this->attrCollection);
        $this->attrCollection->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $this->attrCollection->expects($this->any())->method('getItems')->willReturn([]);
        $this->entityModel = $this->createPartialMock(
            Product::class,
            [
                'getErrorAggregator',
                'getNewSku',
                'getOldSku',
                'getNextBunch',
                'isRowAllowedToImport',
                'getRowScope'
            ]
        );
        $this->skuStorage = $this->createMock(Product\SkuStorage::class);
        $this->entityModel->method('getErrorAggregator')->willReturn($this->getErrorAggregatorObject());
        $this->params = [
            0 => $this->entityModel,
            1 => 'grouped'
        ];
        $this->links = $this->createMock(Links::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->configMock->expects($this->once())
            ->method('getComposableTypes')
            ->willReturn(['simple', 'virtual', 'downloadable']);
        $entityAttributes = [
            [
                'attribute_set_name' => 'attribute_id',
                'attribute_id' => 'attributeSetName',
            ]
        ];
        $this->connection = $this->createPartialMockWithReflection(
            Mysql::class,
            ['select', 'fetchAll', 'fetchPairs', 'insertOnDuplicate', 'delete', 'quoteInto', 'joinLeft']
        );
        $this->select = $this->createPartialMock(
            Select::class,
            ['from', 'where', 'joinLeft', 'getConnection']
        );
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->connection->expects($this->any())->method('select')->willReturn($this->select);
        $connectionMock = $this->createMock(Mysql::class);
        $connectionMock->expects($this->any())->method('quoteInto')->willReturn('query');
        $this->select->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->connection->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->connection->expects($this->any())->method('delete')->willReturnSelf();
        $this->connection->expects($this->any())->method('quoteInto')->willReturn('');
        $this->connection->expects($this->any())->method('fetchAll')->willReturn($entityAttributes);
        $this->resource = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->resource->expects($this->any())->method('getTableName')->willReturn('tableName');
        $objects = [
            [
                ConfigInterface::class,
                $this->createMock(ConfigInterface::class)
            ],
            [
                SkuStorage::class,
                $this->createMock(SkuStorage::class)
            ]
        ];
        $this->objectManagerHelper->prepareObjectManager($objects);
        $this->grouped = $this->objectManagerHelper->getObject(
            Grouped::class,
            [
                'attrSetColFac' => $this->setCollectionFactory,
                'prodAttrColFac' => $this->attrCollectionFactory,
                'resource' => $this->resource,
                'params' => $this->params,
                'links' => $this->links,
                'config' => $this->configMock,
                'skuStorage' => $this->skuStorage
            ]
        );
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $entityMetadataMock = $this->createMock(EntityMetadata::class);
        $metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);
        $entityMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $entityMetadataMock->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn('entity_id');
        $reflection = new \ReflectionClass(Grouped::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setValue($this->grouped, $metadataPoolMock);
    }

    /**
     * Test for method saveData()
     *
     * @param array $skus
     * @param array $bunch
     *
     * @return void
     */
    #[DataProvider('saveDataProvider')]
    public function testSaveData($skus, $bunch): void
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->willReturn($skus['newSku']);
        $this->entityModel->expects($this->never())->method('getOldSku');

        $this->skuStorage->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($sku) use ($skus) {
                return isset($skus['oldSku'][$sku]);
            });

        $this->skuStorage->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($sku) use ($skus) {
                return $skus['oldSku'][$sku] ?? null;
            });

        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->willReturn($attributes);

        $callCount = 0;
        $this->entityModel
            ->method('getNextBunch')
            ->willReturnCallback(function () use (&$callCount, $bunch) {
                return $callCount++ === 0 ? [$bunch] : null;
            });
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->willReturn(true);
        $this->entityModel->expects($this->any())->method('getRowScope')->willReturn(Product::SCOPE_DEFAULT);

        $this->links->expects($this->once())->method('saveLinksData');
        $this->grouped->saveData();
    }

    /**
     * Data provider for saveData()
     *
     * @return array
     */
    public static function saveDataProvider(): array
    {
        return [
            [
                'skus' => [
                    'newSku' => [
                        'sku_assoc1' => ['entity_id' => 1, 'type_id' => 'simple'],
                        'productsku' => ['entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => ['sku_assoc2' => ['entity_id' => 2, 'type_id' => 'simple']]
                ],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                    'sku' => 'productsku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => [
                    'newSku' => [
                        'productsku' => ['entity_id' => 1, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => []
                ],
                'bunch' => [
                    'associated_skus' => '',
                    'sku' => 'productsku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => ['newSku' => [],'oldSku' => []],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                    'sku' => 'productsku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => [
                    'newSku' => [
                        'sku_assoc1' => ['entity_id' => 1, 'type_id' => 'simple'],
                        'productsku' => ['entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => []
                ],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1',
                    'sku' => 'productsku',
                    'product_type' => 'simple'
                ]
            ]
        ];
    }

    /**
     * Test saveData() with store row scope
     *
     * @return void
     */
    public function testSaveDataScopeStore(): void
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->willReturn(
            [
                'sku_assoc1' => ['entity_id' => 1, 'type_id' => 'simple'],
                'productsku' => ['entity_id' => 2, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
            ]
        );
        $oldSkusData = [
            'sku_assoc2' => ['entity_id' => 3, 'type_id' => 'simple']
        ];
        $this->entityModel->expects($this->never())->method('getOldSku');

        $this->skuStorage->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($sku) use ($oldSkusData) {
                return isset($oldSkusData[$sku]);
            });

        $this->skuStorage->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($sku) use ($oldSkusData) {
                return $oldSkusData[$sku] ?? null;
            });

        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->willReturn($attributes);

        $bunch = [
            [
                'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                'sku' => 'productsku',
                'product_type' => 'grouped'
            ]
        ];
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->willReturn(true);
        $callCount = 0;
        $this->entityModel
            ->method('getNextBunch')
            ->willReturnCallback(function () use (&$callCount, $bunch) {
                return $callCount++ === 0 ? $bunch : null;
            });
        $this->entityModel
            ->method('getRowScope')
            ->willReturnOnConsecutiveCalls(Product::SCOPE_DEFAULT, Product::SCOPE_STORE);

        $this->links->expects($this->once())->method('saveLinksData');
        $this->grouped->saveData();
    }

    /**
     * Test saveData() with composite product associated with a grouped product
     *
     * @return void
     */
    public function testSaveDataAssociatedComposite(): void
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->willReturn(
            [
                'sku_assoc1' => ['entity_id' => 1, 'type_id' => 'configurable'],
                'productsku' => ['entity_id' => 2, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
            ]
        );
        $this->entityModel->expects($this->never())->method('getOldSku');
        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->willReturn($attributes);

        $bunch = [
            [
                'associated_skus' => 'sku_assoc1=1',
                'sku' => 'productsku',
                'product_type' => 'grouped'
            ]
        ];

        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->willReturn(true);
        $callCount = 0;
        $this->entityModel
            ->method('getNextBunch')
            ->willReturnCallback(function () use (&$callCount, $bunch) {
                return $callCount++ === 0 ? $bunch : null;
            });
        $this->entityModel
            ->method('getRowScope')
            ->willReturnOnConsecutiveCalls(Product::SCOPE_DEFAULT, Product::SCOPE_STORE);

        $expectedLinkData = [
            'product_ids' => [],
            'attr_product_ids' => [],
            'position' => [],
            'qty' => [],
            'relation' => []
        ];
        $this->links->expects($this->once())->method('saveLinksData')->with($expectedLinkData);
        $this->grouped->saveData();
    }
}
