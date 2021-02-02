<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_attributeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_collectionFactory;

    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadataPoolMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->once())->method('from')->with('catalog_category_entity');
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $connection->expects($this->once())->method('select')->willReturn($select);
        $this->_resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_resource->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'catalog'
        )->willReturn(
            $connection
        );
        $this->_resource->expects(
            $this->once()
        )->method(
            'getTableName'
        )->with(
            'catalog_category_entity'
        )->willReturnArgument(
            0
        );
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_attributeConfig = $this->createMock(\Magento\Catalog\Model\Attribute\Config::class);
        $this->_collectionFactory = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Category\Collection\Factory::class
        );

        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = $objectHelper->getObject(
            \Magento\Catalog\Model\ResourceModel\Category\Tree::class,
            [
                'resource' => $this->_resource,
                'eventManager' => $eventManager,
                'attributeConfig' => $this->_attributeConfig,
                'collectionFactory' => $this->_collectionFactory,
                'metadataPool' => $this->metadataPoolMock,
            ]
        );
    }

    public function testGetCollection()
    {
        $attributes = ['attribute_one', 'attribute_two'];
        $this->_attributeConfig->expects(
            $this->once()
        )->method(
            'getAttributeNames'
        )->with(
            'catalog_category'
        )->willReturn(
            $attributes
        );
        $collection = $this->getCollectionMock();
        $collection->expects($this->once())->method('addAttributeToSelect')->with($attributes);
        $this->_collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->assertSame($collection, $this->_model->getCollection());
        // Makes sure the value is calculated only once
        $this->assertSame($collection, $this->_model->getCollection());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCollectionMock()
    {
        return $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
    }

    public function testSetCollection()
    {
        $collection = $this->getCollectionMock();
        $this->_model->setCollection($collection);

        $this->assertSame($collection, $this->_model->getCollection());
    }

    public function testCallCleaningDuringSetCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Tree $model */
        $model = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Category\Tree::class, ['_clean']);
        $model->expects($this->once())->method('_clean')->willReturnSelf();

        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
    }

    public function testAddCollectionData()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('join')->willReturnSelf();
        $select->expects($this->any())->method('joinInner')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();

        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->any())->method('select')->willReturn($select);
        $connection->expects($this->any())->method('fetchCol')->willReturn([]);

        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($connection);
        $resource->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $attributeConfig = $this->createMock(\Magento\Catalog\Model\Attribute\Config::class);

        $attributes = ['attribute_one', 'attribute_two'];
        $attributeConfig->expects($this->once())
            ->method('getAttributeNames')
            ->with('catalog_category')
            ->willReturn($attributes);

        $collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->expects($this->never())->method('getAllIds')->willReturn([]);
        $collection->expects($this->once())->method('getAllIdsSql')->willReturn($select);
        $collectionFactory = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Collection\Factory::class);
        $collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getId')->willReturn(1);

        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $categoryMetadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('id');
        $this->metadataPoolMock
            ->expects($this->any())
            ->method('getMetadata')
            ->with(CategoryInterface::class)
            ->willReturn($categoryMetadataMock);

        $model = $objectHelper->getObject(
            \Magento\Catalog\Model\ResourceModel\Category\Tree::class,
            [
                'storeManager' => $storeManager,
                'resource' => $resource,
                'eventManager' => $eventManager,
                'attributeConfig' => $attributeConfig,
                'collectionFactory' => $collectionFactory,
                'metadataPool' => $this->metadataPoolMock
            ]
        );

        $nodeMock = $this->createPartialMock(\Magento\Framework\Data\Tree\Node::class, ['getId', 'getPath']);
        $nodeMock->expects($this->any())->method('getId')->willReturn(1);
        $nodeMock->expects($this->once())->method('getPath')->willReturn([]);

        $model->addNode($nodeMock);

        $this->assertSame($model, $model->addCollectionData(null, false, [], false, true));
    }
}
