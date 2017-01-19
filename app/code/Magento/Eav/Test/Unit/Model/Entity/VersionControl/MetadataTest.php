<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\VersionControl;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for version control metadata model.
 */
class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\VersionControl\Metadata
     */
    protected $metadata;

    /**
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->model = $this->getMock(
            \Magento\Framework\Model\AbstractModel::class,
            ['getResource', 'getAttributes'],
            [],
            '',
            false
        );

        $this->resource = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getConnection', 'getEntityTable']
        );

        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false
        );

        $this->model->expects($this->any())->method('getResource')->willReturn($this->resource);

        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $this->metadata = $objectManager->getObject(
            \Magento\Eav\Model\Entity\VersionControl\Metadata::class
        );
    }

    public function testGetFields()
    {
        $entityTable = 'entity_table';

        $expectedDescribedTable = ['field1' => null, 'field2' => null];
        $expectedAttributes = ['attribute1' => 'value1', 'attribute2' => 'value2'];

        $expectedResults = array_merge($expectedDescribedTable, $expectedAttributes);

        $this->resource->expects($this->any())->method('getEntityTable')->willReturn($entityTable);

        $this->connection->expects($this->once())->method('describeTable')->with($entityTable)->willReturn(
            $expectedDescribedTable
        );

        $this->model->expects($this->any())->method('getAttributes')->willReturn($expectedAttributes);
        //check that fields load with null initial value
        $this->assertEquals(
            array_fill_keys(array_keys($expectedResults), null),
            $this->metadata->getFields($this->model)
        );

        // Testing loading data from cache.
        $this->connection->expects($this->never())->method('describeTable');

        $this->assertEquals(
            array_fill_keys(array_keys($expectedResults), null),
            $this->metadata->getFields($this->model)
        );
    }
}
