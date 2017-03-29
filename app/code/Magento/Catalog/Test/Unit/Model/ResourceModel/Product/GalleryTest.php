<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

/**
 * Unit test for product media gallery resource.
 */
class GalleryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\DB\Select | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute | \PHPUnit_Framework_MockObject_MockObject
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
        'metadata' => ['DATA_TYPE' => 'text', 'NULLABLE' => true],
    ];

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->connection = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [],
            [],
            '',
            false
        );
        $this->connection->expects($this->any())
            ->method('setCacheAdapter');

        $metadata = $this->getMock(
            \Magento\Framework\EntityManager\EntityMetadata::class,
            [],
            [],
            '',
            false
        );
        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $metadataPool = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadata);

        $resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $resource->expects($this->any())->method('getTableName')->willReturn('table');
        $this->resource = $objectManager->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Gallery::class,
            [
                'metadataPool' => $metadataPool,
                'resource' => $resource
            ]
        );
        $this->product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->select = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $this->attribute = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            [],
            '',
            false
        );
    }

    public function testLoadDataFromTableByValueId()
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
            'video_metadata_default' => 'metadata',
        ];
        $leftJoinTables = [
            0 => [
                0 => [
                        'store_value' => 'catalog_product_entity_media_gallery_value_video',
                    ],
                1 => 'main.value_id = store_value.value_id AND store_value.store_id = 0',
                2 => [
                        'video_provider' => 'provider',
                        'video_url' => 'url',
                        'video_title' => 'title',
                        'video_description' => 'description',
                        'video_metadata' => 'metadata',
                    ],
            ],
        ];
        $whereCondition = null;
        $getTableReturnValue = 'table';
        $this->connection->expects($this->once())->method('select')->will($this->returnValue($this->select));
        $this->select->expects($this->at(0))->method('from')->with(
            [
                'main' => $getTableReturnValue,
            ],
            [
                'value_id' => 'value_id',
                'video_provider_default' => 'provider',
                'video_url_default' => 'url',
                'video_title_default' => 'title',
                'video_description_default' => 'description',
                'video_metadata_default' => 'metadata',
            ]
        )->willReturnSelf();
        $this->select->expects($this->at(1))->method('where')->with(
            'main.value_id IN(?)',
            $ids
        )->willReturnSelf();
        $this->select->expects($this->at(2))->method('where')->with(
            'main.store_id = ?',
            $storeId
        )->willReturnSelf();
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
                'video_metadata' => 'meta one',
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
                'video_metadata' => '',
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

    public function testLoadDataFromTableByValueIdNoColsWithWhere()
    {
        $tableNameAlias = 'catalog_product_entity_media_gallery_value_video';
        $ids = [5, 8];
        $storeId = 0;
        $cols = null;
        $leftJoinTables = [
            0 => [
                    0 => [
                            'store_value' => 'catalog_product_entity_media_gallery_value_video',
                        ],
                    1 => 'main.value_id = store_value.value_id AND store_value.store_id = 0',
                    2 => [
                            'video_provider' => 'provider',
                            'video_url' => 'url',
                            'video_title' => 'title',
                            'video_description' => 'description',
                            'video_metadata' => 'metadata',
                        ],
                ],
        ];
        $whereCondition = 'main.store_id = ' . $storeId;
        $getTableReturnValue = 'table';

        $this->connection->expects($this->once())->method('select')->will($this->returnValue($this->select));
        $this->select->expects($this->at(0))->method('from')->with(
            [
                'main' => $getTableReturnValue,
            ],
            '*'
        )->willReturnSelf();

        $this->select->expects($this->at(1))->method('where')->with(
            'main.value_id IN(?)',
            $ids
        )->willReturnSelf();

        $this->select->expects($this->at(2))->method('where')->with(
            'main.store_id = ?',
            $storeId
        )->willReturnSelf();

        $this->select->expects($this->at(3))->method('where')->with(
            $whereCondition
        )->willReturnSelf();

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
                'video_metadata' => 'meta one',
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
                'video_metadata' => '',
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

    public function testBindValueToEntityRecordExists()
    {
        $valueId = 14;
        $entityId = 1;
        $this->resource->bindValueToEntity($valueId, $entityId);
    }

    public function testLoadGallery()
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
                'disabled_default' => '0',
            ],
        ];

        $this->connection->expects($this->once())->method('getCheckSql')->with(
            'value.position IS NULL',
            'default_value.position',
            'value.position'
        )->will($this->returnValue($positionCheckSql));
        $this->connection->expects($this->once())->method('select')->will($this->returnValue($this->select));
        $this->select->expects($this->at(0))->method('from')->with(
            [
                'main' => $getTableReturnValue,
            ],
            [
                'value_id',
                'file' => 'value',
                'media_type'
            ]
        )->willReturnSelf();
        $this->select->expects($this->at(1))->method('joinInner')->with(
            ['entity' => $getTableReturnValue],
            'main.value_id = entity.value_id',
            ['entity_id']
        )->willReturnSelf();
        $this->product->expects($this->at(0))->method('getData')->with('entity_id')->willReturn($productId);
        $this->product->expects($this->at(1))->method('getStoreId')->will($this->returnValue($storeId));
        $this->connection->expects($this->exactly(2))->method('quoteInto')->withConsecutive(
            ['value.store_id = ?'],
            ['default_value.store_id = ?']
        )->willReturnOnConsecutiveCalls(
            'value.store_id = ' . $storeId,
            'default_value.store_id = ' . 0
        );
        $this->select->expects($this->at(2))->method('joinLeft')->with(
            ['value' => $getTableReturnValue],
            $quoteInfoReturnValue,
            [
                'label',
                'position',
                'disabled'
            ]
        )->willReturnSelf();
        $this->select->expects($this->at(3))->method('joinLeft')->with(
            ['default_value' => $getTableReturnValue],
            $quoteDefaultInfoReturnValue,
            ['label_default' => 'label', 'position_default' => 'position', 'disabled_default' => 'disabled']
        )->willReturnSelf();
        $this->select->expects($this->at(4))->method('where')->with(
            'main.attribute_id = ?',
            $attributeId
        )->willReturnSelf();
        $this->select->expects($this->at(5))->method('where')->with('main.disabled = 0')->willReturnSelf();
        $this->select->expects($this->at(7))->method('where')
                     ->with('entity.entity_id = ?', $productId)
                     ->willReturnSelf();
        $this->select->expects($this->once())->method('order')
                     ->with($positionCheckSql . ' ' . \Magento\Framework\DB\Select::SQL_ASC)
                     ->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchAll')
                         ->with($this->select)
                         ->willReturn($resultRow);

        $this->assertEquals($resultRow, $this->resource->loadProductGalleryByAttributeId($this->product, $attributeId));
    }

    public function testInsertGalleryValueInStore()
    {
        $data = [
            'value_id' => '8',
            'store_id' => 0,
            'provider' => '',
            'url' => 'https://www.youtube.com/watch?v=abcdfghijk',
            'title' => 'New Title',
            'description' => 'New Description',
            'metadata' => 'New metadata',
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

    public function testDeleteGalleryValueInStore()
    {
        $valueId = 4;
        $entityId = 6;
        $storeId = 1;

        $this->connection->expects($this->exactly(3))->method('quoteInto')->withConsecutive(
            ['value_id = ?', (int)$valueId],
            ['entity_id = ?', (int)$entityId],
            ['store_id = ?', (int)$storeId]
        )->willReturnOnConsecutiveCalls(
            'value_id = ' . $valueId,
            'entity_id = ' . $entityId,
            'store_id = ' . $storeId
        );

        $this->connection->expects($this->once())->method('delete')->with(
            'table',
            'value_id = 4 AND entity_id = 6 AND store_id = 1'
        )->willReturnSelf();

        $this->resource->deleteGalleryValueInStore($valueId, $entityId, $storeId);
    }

    public function testCountImageUses()
    {
        $results = [
            [
                'value_id' => '1',
                'attribute_id' => 90,
                'value' => '/d/o/download_7.jpg',
                'media_type' => 'image',
                'disabled' => '0',
            ],
        ];

        $this->connection->expects($this->once())->method('select')->will($this->returnValue($this->select));
        $this->select->expects($this->at(0))->method('from')->with(
            [
                'main' => 'table',
            ],
            '*'
        )->willReturnSelf();
        $this->select->expects($this->at(1))->method('where')->with(
            'value = ?',
            1
        )->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchAll')
            ->with($this->select)
            ->willReturn($results);
        $this->assertEquals($this->resource->countImageUses(1), count($results));
    }
}
