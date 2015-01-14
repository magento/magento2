<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->readAdapter =
            $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $this->writeAdapter =
            $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Resource\Product\Link',
            ['resource' => $this->resource]
        );
    }

    protected function prepareReadAdapter()
    {
        $this->dbSelect = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);

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
        $result = [100, 200, 300, 400];

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
        $bind = [':product_id' => $parentId, ':link_type_id' => $typeId];

        $fetchedData = [['linked_product_id' => 100], ['linked_product_id' => 500]];

        $result = [$typeId => [100 => 100, 500 => 500]];

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
        $fetchedData = [['product_id' => 55], ['product_id' => 66]];
        $result = [55, 66];

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
