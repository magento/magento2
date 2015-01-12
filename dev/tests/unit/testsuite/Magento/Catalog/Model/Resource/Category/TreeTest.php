<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Category;

class TreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Category\Tree
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $select = $this->getMock('Zend_Db_Select', [], [], '', false);
        $select->expects($this->once())->method('from')->with('catalog_category_entity');
        $connection = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->_resource->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'catalog_write'
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
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->_attributeConfig = $this->getMock(
            'Magento\Catalog\Model\Attribute\Config',
            [],
            [],
            '',
            false
        );
        $this->_collectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\Collection\Factory',
            [],
            [],
            '',
            false
        );
        $this->_model = $objectHelper->getObject(
            'Magento\Catalog\Model\Resource\Category\Tree',
            [
                'resource' => $this->_resource,
                'eventManager' => $eventManager,
                'attributeConfig' => $this->_attributeConfig,
                'collectionFactory' => $this->_collectionFactory
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCollectionMock()
    {
        return $this->getMock('Magento\Catalog\Model\Resource\Category\Collection', [], [], '', false);
    }

    public function testSetCollection()
    {
        $collection = $this->getCollectionMock();
        $this->_model->setCollection($collection);

        $this->assertSame($collection, $this->_model->getCollection());
    }

    public function testCallCleaningDuringSetCollection()
    {
        /** @var \Magento\Catalog\Model\Resource\Category\Tree $model */
        $model = $this->getMock('Magento\Catalog\Model\Resource\Category\Tree', ['_clean'], [], '', false);
        $model->expects($this->once())->method('_clean')->will($this->returnSelf());

        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
    }

    public function testAddCollectionData()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $select = $this->getMock('Zend_Db_Select', [], [], '', false);
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('join')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());

        $connection = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $connection->expects($this->any())->method('select')->will($this->returnValue($select));
        $connection->expects($this->any())->method('fetchCol')->will($this->returnValue([]));

        $resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTableName')->will($this->returnArgument(0));

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $attributeConfig = $this->getMock(
            'Magento\Catalog\Model\Attribute\Config',
            [],
            [],
            '',
            false
        );

        $attributes = ['attribute_one', 'attribute_two'];
        $attributeConfig->expects(
            $this->once()
        )->method(
                'getAttributeNames'
            )->with(
                'catalog_category'
            )->will(
                $this->returnValue($attributes)
            );

        $collection = $this->getMock('Magento\Catalog\Model\Resource\Category\Collection', [], [], '', false);
        $collection->expects($this->never())->method('getAllIds')->will($this->returnValue([]));
        $collectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\Collection\Factory',
            [],
            [],
            '',
            false
        );
        $collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));

        $storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $model = $objectHelper->getObject(
            'Magento\Catalog\Model\Resource\Category\Tree',
            [
                'storeManager' => $storeManager,
                'resource' => $resource,
                'eventManager' => $eventManager,
                'attributeConfig' => $attributeConfig,
                'collectionFactory' => $collectionFactory
            ]
        );

        $nodeMock = $this->getMock('\Magento\Framework\Data\Tree\Node', ['getId', 'getPath'], [], '', false);
        $nodeMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $nodeMock->expects($this->once())->method('getPath')->will($this->returnValue([]));

        $model->addNode($nodeMock);

        $this->assertSame($model, $model->addCollectionData(null, false, [], false, true));
    }
}
