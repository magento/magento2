<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for product media gallery resource.
 */
class GalleryTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var Gallery|MockObject
     */
    protected $resource;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var Select|MockObject
     */
    protected $select;

    /**
     * @var AbstractAttribute|MockObject
     */
    protected $attribute;

    /**
     * @var array
     */
    protected $fields = [
        'value_id' => ['DATA_TYPE' => 'int', 'NULLABLE' => false],
        'store_id' => ['DATA_TYPE' => 'int', 'NULLABLE' => false],
        'provider' => ['DATA_TYPE' => 'varchar', 'NULLABLE' => true],
        'url' => ['DATA_TYPE' => 'text', 'NULLABLE' => true],
        'title' => ['DATA_TYPE' => 'varchar', 'NULLABLE' => true],
        'description' => ['DATA_TYPE' => 'text', 'NULLABLE' => true],
        'metadata' => ['DATA_TYPE' => 'text', 'NULLABLE' => true]
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->connection = $this->createMock(Mysql::class);
        $this->connection->expects($this->any())
            ->method('setCacheAdapter');

        $metadata = $this->createMock(EntityMetadata::class);
        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $metadataPool = $this->createMock(MetadataPool::class);
        $metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadata);

        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->any())->method('getTableName')->willReturn('table');
        $this->resource = $objectManager->getObject(
            Gallery::class,
            [
                'metadataPool' => $metadataPool,
                'resource' => $resource
            ]
        );
        $this->product = $this->createMock(Product::class);
        $this->select = $this->createMock(Select::class);
        $this->attribute = $this->createMock(AbstractAttribute::class);
    }

    /**
     * @return void
     */
    public function testLoadDataFromTableByValueId(): void
    {
        $tableNameAlias = 'catalog_product_entity_media_gallery_value_video';
        $ids = [5, 8];
        $storeId = 0;
        $cols = [
            'value_id' => 'value_id',
            'video_provider_default' => 'provider',
            'video_url_default' => 'url',
            'video_title_default' => 'title',
            'video_description_default' => 'description',
            'video_metadata_default' => 'metadata'
        ];
        $leftJoinTables = [
            0 => [
                0 => [
                    'store_value' => 'catalog_product_entity_media_gallery_value_video'
                ],
                1 => 'main.value_id = store_value.value_id AND store_value.store_id = 0',
                2 => [
                    'video_provider' => 'provider',
                    'video_url' => 'url',
                    'video_title' => 'title',
                    'video_description' => 'description',
                    'video_metadata' => 'metadata'
                ]
            ]
        ];
        $whereCondition = null;
        $getTableReturnValue = 'table';
        $this->connection->expects($this->once())->method('select')->willReturn($this->select);
        $this->select = $this->seletForTableByValueId($getTableReturnValue, $ids, $storeId);
        $resultRow = [
            [
                'value_id' => '4',
                'store_id' => 1,
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title_default' => 'Some first title',
                'video_description_default' => 'Description first',
                'video_metadata_default' => 'meta one',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title' => 'Some first title',
                'video_description' => 'Description first',
                'video_metadata' => 'meta one'
            ],
            [
                'value_id' => '5',
                'store_id' => 0,
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title_default' => 'Some second title',
                'video_description_default' => 'Description second',
                'video_metadata_default' => 'meta two',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title' => 'Some second title',
                'video_description' => 'Description second',
                'video_metadata' => ''
            ]
        ];
        $this->connection->expects($this->once())->method('fetchAll')
            ->with($this->select)
            ->willReturn($resultRow);

        $methodResult = $this->resource->loadDataFromTableByValueId(
            $tableNameAlias,
            $ids,
            $storeId,
            $cols,
            $leftJoinTables,
            $whereCondition
        );
        $this->assertEquals($resultRow, $methodResult);
    }

    /**
     * @param $getTableReturnValue
     * @param $ids
     * @param $storeId
     * @return Select
     */
    protected function seletForTableByValueId($getTableReturnValue, $ids, $storeId)
    {
        $this->select
            ->method('from')
            ->with(
                [
                    'main' => $getTableReturnValue,
                ],
                [
                    'value_id' => 'value_id',
                    'video_provider_default' => 'provider',
                    'video_url_default' => 'url',
                    'video_title_default' => 'title',
                    'video_description_default' => 'description',
                    'video_metadata_default' => 'metadata'
                ]
            )
            ->willReturn($this->select);
        $this->select
            ->method('where')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($ids, $storeId) {
                    if ($arg1 == 'main.value_id IN(?)' && $arg2 == $ids) {
                        return $this->select;
                    } elseif ($arg1 == 'main.store_id = ?' && $arg2 == $storeId) {
                        return $this->select;
                    }
                }
            );
        return $this->select;
    }

    /**
     * @return void
     */
    public function testLoadDataFromTableByValueIdNoColsWithWhere(): void
    {
        $tableNameAlias = 'catalog_product_entity_media_gallery_value_video';
        $ids = [5, 8];
        $storeId = 0;
        $cols = null;
        $leftJoinTables = [
            0 => [
                0 => [
                    'store_value' => 'catalog_product_entity_media_gallery_value_video'
                ],
                1 => 'main.value_id = store_value.value_id AND store_value.store_id = 0',
                2 => [
                    'video_provider' => 'provider',
                    'video_url' => 'url',
                    'video_title' => 'title',
                    'video_description' => 'description',
                    'video_metadata' => 'metadata'
                ]
            ]
        ];
        $whereCondition = 'main.store_id = ' . $storeId;
        $getTableReturnValue = 'table';

        $this->connection->expects($this->once())->method('select')->willReturn($this->select);

        $this->select
            ->method('from')
            ->with(
                [
                    'main' => $getTableReturnValue
                ],
                '*'
            )
            ->willReturn($this->select);
        $this->select
            ->method('where')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($ids, $storeId, $whereCondition) {
                    if ($arg1 == 'main.value_id IN(?)' && $arg2 == $ids) {
                        return $this->select;
                    } elseif ($arg1 == 'main.store_id = ?' && $arg2 == $storeId) {
                        return $this->select;
                    } elseif ($arg1 == $whereCondition) {
                        return $this->select;
                    }
                }
            );

        $resultRow = [
            [
                'value_id' => '4',
                'store_id' => 1,
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title_default' => 'Some first title',
                'video_description_default' => 'Description first',
                'video_metadata_default' => 'meta one',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title' => 'Some first title',
                'video_description' => 'Description first',
                'video_metadata' => 'meta one'
            ],
            [
                'value_id' => '5',
                'store_id' => 0,
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title_default' => 'Some second title',
                'video_description_default' => 'Description second',
                'video_metadata_default' => 'meta two',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title' => 'Some second title',
                'video_description' => 'Description second',
                'video_metadata' => ''
            ]
        ];

        $this->connection->expects($this->once())->method('fetchAll')
            ->with($this->select)
            ->willReturn($resultRow);

        $methodResult = $this->resource->loadDataFromTableByValueId(
            $tableNameAlias,
            $ids,
            $storeId,
            $cols,
            $leftJoinTables,
            $whereCondition
        );

        $this->assertEquals($resultRow, $methodResult);
    }

    /**
     * @return void
     */
    public function testBindValueToEntityRecordExists(): void
    {
        $valueId = 14;
        $entityId = 1;
        $this->resource->bindValueToEntity($valueId, $entityId);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testLoadGallery(): void
    {
        $productId = 5;
        $storeId = 1;
        $attributeId = 6;
        $getTableReturnValue = 'table';
        $quoteInfoReturnValue =
            'main.value_id = value.value_id AND value.store_id = ' . $storeId
            . ' AND value.entity_id = entity.entity_id';
        $quoteDefaultInfoReturnValue =
            'main.value_id = default_value.value_id AND default_value.store_id = 0'
            . ' AND default_value.entity_id = entity.entity_id';

        $positionCheckSql = 'testchecksql';
        $resultRow = [
            [
                'value_id' => '1',
                'file' => '/d/o/download_7.jpg',
                'label' => null,
                'position' => '1',
                'disabled' => '0',
                'label_default' => null,
                'position_default' => '1',
                'disabled_default' => '0'
            ]
        ];

        $this->connection->expects($this->once())->method('getCheckSql')->with(
            'value.position IS NULL',
            'default_value.position',
            'value.position'
        )->willReturn($positionCheckSql);
        $this->connection->expects($this->once())->method('select')->willReturn($this->select);
        $this->product
            ->method('getData')
            ->with('entity_id')
            ->willReturn($productId);
        $this->product
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->connection->expects($this->exactly(2))->method('quoteInto')
            ->willReturnCallback(
                function ($arg) use ($storeId) {
                    if ($arg == 'value.store_id = ?') {
                        return 'value.store_id = ' . $storeId;
                    } elseif ($arg == 'default_value.store_id = ?') {
                        return 'default_value.store_id = ' . 0;
                    }
                }
            );

        $this->connection->expects($this->any())->method('getIfNullSql')->willReturnMap(
            [
                [
                    '`value`.`label`',
                    '`default_value`.`label`',
                    'IFNULL(`value`.`label`, `default_value`.`label`)'
                ],
                [
                    '`value`.`position`',
                    '`default_value`.`position`',
                    'IFNULL(`value`.`position`, `default_value`.`position`)'
                ],
                [
                    '`value`.`disabled`',
                    '`default_value`.`disabled`',
                    'IFNULL(`value`.`disabled`, `default_value`.`disabled`)'
                ]
            ]
        );
        $this->select
            ->method('from')
            ->with(
                [
                    'main' => $getTableReturnValue,
                ],
                [
                    'value_id',
                    'file' => 'value',
                    'media_type'
                ]
            )
            ->willReturn($this->select);
        $this->select
            ->method('where')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($attributeId, $productId) {
                    if ($arg1 == 'main.attribute_id = ?' && $arg2 == $attributeId) {
                        return $this->select;
                    } elseif ($arg1 == 'main.disabled = 0') {
                        return $this->select;
                    } elseif ($arg1 == 'entity.entity_id = ?' && $arg2 == $productId) {
                        return $this->select;
                    }
                }
            );
        $this->select
            ->method('columns')
            ->with(
                [
                    'label' => 'IFNULL(`value`.`label`, `default_value`.`label`)',
                    'position' => 'IFNULL(`value`.`position`, `default_value`.`position`)',
                    'disabled' => 'IFNULL(`value`.`disabled`, `default_value`.`disabled`)',
                    'label_default' => 'default_value.label',
                    'position_default' => 'default_value.position',
                    'disabled_default' => 'default_value.disabled'
                ]
            )
            ->willReturn($this->select);
        $this->select
            ->method('joinLeft')
            ->willReturnCallback(
                function (
                    $arg1,
                    $arg2
                ) use (
                    $getTableReturnValue,
                    $quoteInfoReturnValue,
                    $quoteDefaultInfoReturnValue
                ) {
                    if ($arg1 === ['value' => $getTableReturnValue]
                        && $arg2 === $quoteInfoReturnValue) {
                        return $this->select;
                    } elseif ($arg1 === ['default_value' => $getTableReturnValue] &&
                        $arg2 === $quoteDefaultInfoReturnValue) {
                        return $this->select;
                    }
                }
            );
        $this->select
            ->method('joinInner')
            ->with(['entity' => $getTableReturnValue], 'main.value_id = entity.value_id', ['entity_id'])
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('order')
            ->with($positionCheckSql . ' ' . Select::SQL_ASC)
            ->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchAll')
            ->with($this->select)
            ->willReturn($resultRow);

        $this->assertEquals($resultRow, $this->resource->loadProductGalleryByAttributeId($this->product, $attributeId));
    }

    /**
     * @return void
     */
    public function testInsertGalleryValueInStore(): void
    {
        $data = [
            'value_id' => '8',
            'store_id' => 0,
            'provider' => '',
            'url' => 'https://www.youtube.com/watch?v=abcdfghijk',
            'title' => 'New Title',
            'description' => 'New Description',
            'metadata' => 'New metadata'
        ];

        $this->connection->expects($this->once())->method('describeTable')->willReturn($this->fields);
        $this->connection->expects($this->any())->method('prepareColumnValue')->willReturnOnConsecutiveCalls(
            '8',
            0,
            '',
            'https://www.youtube.com/watch?v=abcdfghijk',
            'New Title',
            'New Description',
            'New metadata'
        );

        $this->resource->insertGalleryValueInStore($data);
    }

    /**
     * @return void
     */
    public function testDeleteGalleryValueInStore(): void
    {
        $valueId = 4;
        $entityId = 6;
        $storeId = 1;

        $this->connection->expects($this->exactly(3))->method('quoteInto')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($valueId, $entityId, $storeId) {
                    if ($arg1 == 'value_id = ?' && $arg2 == (int)$valueId) {
                        return 'value_id = ' . $valueId;
                    } elseif ($arg1 == 'entity_id = ?' && $arg2 == (int)$entityId) {
                        return 'entity_id = ' . $entityId;
                    } elseif ($arg1 == 'store_id = ?' && $arg2 == (int)$storeId) {
                        return 'store_id = ' . $storeId;
                    }
                }
            );

        $this->connection->expects($this->once())->method('delete')->with(
            'table',
            'value_id = 4 AND entity_id = 6 AND store_id = 1'
        )->willReturnSelf();

        $this->resource->deleteGalleryValueInStore($valueId, $entityId, $storeId);
    }

    /**
     * @return void
     */
    public function testCountImageUses(): void
    {
        $results = [
            [
                'value_id' => '1',
                'attribute_id' => 90,
                'value' => '/d/o/download_7.jpg',
                'media_type' => 'image',
                'disabled' => '0'
            ]
        ];

        $this->connection->expects($this->once())->method('select')->willReturn($this->select);
        $this->select
            ->method('from')
            ->with(
                [
                    'main' => 'table',
                ],
                '*'
            )
            ->willReturn($this->select);
        $this->select
            ->method('where')
            ->with('value = ?', 1)
            ->willReturn($this->select);
        $this->connection->expects($this->once())->method('fetchAll')
            ->with($this->select)
            ->willReturn($results);
        $this->assertCount($this->resource->countImageUses(1), $results);
    }
}
