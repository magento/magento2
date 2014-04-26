<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $select->expects($this->once())->method('from')->with('catalog_category_entity');
        $connection = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
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
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $this->_attributeConfig = $this->getMock(
            'Magento\Catalog\Model\Attribute\Config',
            array(),
            array(),
            '',
            false
        );
        $this->_collectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\Collection\Factory',
            array(),
            array(),
            '',
            false
        );
        $this->_model = $objectHelper->getObject(
            'Magento\Catalog\Model\Resource\Category\Tree',
            array(
                'resource' => $this->_resource,
                'eventManager' => $eventManager,
                'attributeConfig' => $this->_attributeConfig,
                'collectionFactory' => $this->_collectionFactory
            )
        );
    }

    public function testGetCollection()
    {
        $attributes = array('attribute_one', 'attribute_two');
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
        return $this->getMock('Magento\Catalog\Model\Resource\Category\Collection', array(), array(), '', false);
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
        $model = $this->getMock('Magento\Catalog\Model\Resource\Category\Tree', array('_clean'), array(), '', false);
        $model->expects($this->once())->method('_clean')->will($this->returnSelf());

        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
        $this->assertEquals($model, $model->setCollection($this->getCollectionMock()));
    }
}
