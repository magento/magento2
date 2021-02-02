<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dbSelect;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connection =
            $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Link::class,
            ['resource' => $this->resource]
        );
    }

    protected function prepareAdapter()
    {
        $this->dbSelect = $this->createMock(\Magento\Framework\DB\Select::class);

        // method flow
        $this->resource->expects(
            $this->at(0)
        )->method(
            'getConnection'
        )->willReturn(
            $this->connection
        );

        $this->connection->expects($this->once())->method('select')->willReturn($this->dbSelect);
    }

    public function testGetAttributesByType()
    {
        $typeId = 4;
        $result = [100, 200, 300, 400];

        $this->prepareAdapter();
        $this->dbSelect->expects($this->once())->method('from')->willReturn($this->dbSelect);
        $this->dbSelect->expects(
            $this->atLeastOnce()
        )->method(
            'where'
        )->with(
            'link_type_id = ?',
            $typeId
        )->willReturn(
            $this->dbSelect
        );
        $this->connection->expects($this->once())->method('fetchAll')->willReturn($result);
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
        )->willReturn(
            $resultTable
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
        $this->prepareAdapter();
        $this->dbSelect->expects($this->once())->method('from')->willReturn($this->dbSelect);
        $this->dbSelect->expects($this->atLeastOnce())->method('where')->willReturn($this->dbSelect);
        $this->connection->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $this->dbSelect,
            $bind
        )->willReturn(
            $fetchedData
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
        $this->prepareAdapter();
        $this->dbSelect->expects($this->once())->method('from')->willReturn($this->dbSelect);
        $this->dbSelect->expects($this->any())->method('where')->willReturn($this->dbSelect);

        $this->connection->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $this->dbSelect
        )->willReturn(
            $fetchedData
        );

        $this->assertEquals($result, $this->model->getParentIdsByChild($childId, $typeId));
    }
}
