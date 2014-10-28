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
namespace Magento\Catalog\Model\Resource\Product;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Link
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readAdapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeAdapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbSelect;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->resource = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
        $this->readAdapter =
            $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', array(), array(), '', false);
        $this->writeAdapter =
            $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', array(), array(), '', false);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Resource\Product\Link',
            array('resource' => $this->resource)
        );
    }

    protected function prepareReadAdapter()
    {
        $this->dbSelect = $this->getMock('Magento\Framework\DB\Select', array(), array(), '', false);

        // method flow
        $this->resource->expects(
            $this->at(0)
        )->method(
            'getConnection'
        )->with(
            'core_write'
        )->will(
            $this->returnValue($this->writeAdapter)
        );
        $this->resource->expects(
            $this->at(1)
        )->method(
            'getConnection'
        )->with(
            'core_read'
        )->will(
            $this->returnValue($this->readAdapter)
        );

        $this->readAdapter->expects($this->once())->method('select')->will($this->returnValue($this->dbSelect));
    }

    public function testGetAttributesByType()
    {
        $typeId = 4;
        $result = array(100, 200, 300, 400);

        $this->prepareReadAdapter();
        $this->dbSelect->expects($this->once())->method('from')->will($this->returnValue($this->dbSelect));
        $this->dbSelect->expects(
            $this->atLeastOnce()
        )->method(
            'where'
        )->with(
            'link_type_id = ?',
            $typeId
        )->will(
            $this->returnValue($this->dbSelect)
        );
        $this->readAdapter->expects($this->once())->method('fetchAll')->will($this->returnValue($result));
        $this->assertEquals($result, $this->model->getAttributesByType($typeId));
    }

    public function testGetAttributeTypeTable()
    {
        $inputTable = 'megatable';
        $resultTable = 'advancedTable';

        $this->resource->expects(
            $this->once()
        )->method(
            'getTableName'
        )->with(
            'catalog_product_link_attribute_' . $inputTable
        )->will(
            $this->returnValue($resultTable)
        );
        $this->assertEquals($resultTable, $this->model->getAttributeTypeTable($inputTable));
    }

    public function testGetChildrenIds()
    {
        //prepare mocks and data
        $parentId = 100;
        $typeId = 4;
        $bind = array(':product_id' => $parentId, ':link_type_id' => $typeId);

        $fetchedData = array(array('linked_product_id' => 100), array('linked_product_id' => 500));

        $result = array($typeId => array(100 => 100, 500 => 500));

        // method flow
        $this->prepareReadAdapter();
        $this->dbSelect->expects($this->once())->method('from')->will($this->returnValue($this->dbSelect));
        $this->dbSelect->expects($this->atLeastOnce())->method('where')->will($this->returnValue($this->dbSelect));
        $this->readAdapter->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $this->dbSelect,
            $bind
        )->will(
            $this->returnValue($fetchedData)
        );

        $this->assertEquals($result, $this->model->getChildrenIds($parentId, $typeId));
    }

    public function testGetParentIdsByChild()
    {
        $childId = 234;
        $typeId = 4;
        $fetchedData = array(array('product_id' => 55), array('product_id' => 66));
        $result = array(55, 66);

        // method flow
        $this->prepareReadAdapter();
        $this->dbSelect->expects($this->once())->method('from')->will($this->returnValue($this->dbSelect));
        $this->dbSelect->expects($this->any())->method('where')->will($this->returnValue($this->dbSelect));

        $this->readAdapter->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $this->dbSelect
        )->will(
            $this->returnValue($fetchedData)
        );

        $this->assertEquals($result, $this->model->getParentIdsByChild($childId, $typeId));
    }
}
