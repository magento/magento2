<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Attribute\Config;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Collection\Factory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TreeTest extends TestCase
{
    /**
     * @var Tree
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_resource;

    /**
     * @var MockObject
     */
    protected $_attributeConfig;

    /**
     * @var MockObject
     */
    protected $_collectionFactory;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPoolMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);
        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('from')->with('catalog_category_entity');
        $connection = $this->createMock(Mysql::class);
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $this->_resource = $this->createMock(ResourceConnection::class);
        $this->_resource->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'catalog'
        )->will(
            $this->returnValue($connection)
        );
        $this->_resource->expects(
            $this->once()
        )->method(
            'getTableName'
        )->with(
            'catalog_category_entity'
        )->will(
            $this->returnArgument(0)
        );
        $eventManager = $this->createMock(ManagerInterface::class);
        $this->_attributeConfig = $this->createMock(Config::class);
        $this->_collectionFactory = $this->createMock(
            Factory::class
        );

        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = $objectHelper->getObject(
            Tree::class,
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
        )->will(
            $this->returnValue($attributes)
        );
        $collection = $this->getCollectionMock();
        $collection->expects($this->once())->method('addAttributeToSelect')->with($attributes);
        $this->_collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->assertSame($collection, $this->_model->getCollection());
        // Makes sure the value is calculated only once
        $this->assertSame($collection, $this->_model->getCollection());
    }

    /**
     * @return MockObject
     */
    protected function getCollectionMock()
    {
        return $this->createMock(Collection::class);
    }

    public function testSetCollection()
    {
        $collection = $this->getCollectionMock();
        $this->_model->setCollection($collection);

        $this->assertSame($collection, $this->_model->getCollection());
    }

    public function testCallCleaningDuringSetCollection()
    {
        /** @var Tree $model */
        $model = $this->createPartialMock(Tree::class, ['_clean']);
        $model->expects($this->once())->method('_clean')->will($this->returnSelf());

        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
    }

    public function testAddCollectionData()
    {
        $objectHelper = new ObjectManager($this);
        $select = $this->createMock(Select::class);
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('join')->will($this->returnSelf());
        $select->expects($this->any())->method('joinInner')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->any())->method('select')->will($this->returnValue($select));
        $connection->expects($this->any())->method('fetchCol')->will($this->returnValue([]));

        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTableName')->will($this->returnArgument(0));

        $eventManager = $this->createMock(ManagerInterface::class);
        $attributeConfig = $this->createMock(Config::class);

        $attributes = ['attribute_one', 'attribute_two'];
        $attributeConfig->expects($this->once())
            ->method('getAttributeNames')
            ->with('catalog_category')
            ->will($this->returnValue($attributes));

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->never())->method('getAllIds')->will($this->returnValue([]));
        $collection->expects($this->once())->method('getAllIdsSql')->will($this->returnValue($select));
        $collectionFactory = $this->createMock(Factory::class);
        $collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $store = $this->createMock(Store::class);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));

        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

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
            Tree::class,
            [
                'storeManager' => $storeManager,
                'resource' => $resource,
                'eventManager' => $eventManager,
                'attributeConfig' => $attributeConfig,
                'collectionFactory' => $collectionFactory,
                'metadataPool' => $this->metadataPoolMock
            ]
        );

        $nodeMock = $this->createPartialMock(Node::class, ['getId', 'getPath']);
        $nodeMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $nodeMock->expects($this->once())->method('getPath')->will($this->returnValue([]));

        $model->addNode($nodeMock);

        $this->assertSame($model, $model->addCollectionData(null, false, [], false, true));
    }
}
