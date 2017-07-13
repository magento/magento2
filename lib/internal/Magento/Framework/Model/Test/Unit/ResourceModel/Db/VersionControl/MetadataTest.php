<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\VersionControl;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Version Control MetadataTest
 */
class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata
     */
    protected $entityMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\AbstractModel
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $this->getMock(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            [],
            '',
            false
        );
        $this->resource = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            "",
            false,
            false,
            true,
            ['getConnection', 'getMainTable']
        );
        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            "",
            false,
            false
        );
        $this->model->expects($this->any())->method('getResource')->willReturn($this->resource);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->entityMetadata = $objectManager->getObject(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata::class
        );
    }

    public function testGetFields()
    {
        $mainTable = 'main_table';
        $expectedDescribedTable = ['described_table' => null];
        $this->resource->expects($this->any())->method('getMainTable')->willReturn($mainTable);
        $this->connection->expects($this->once())->method('describeTable')->with($mainTable)->willReturn(
            $expectedDescribedTable
        );
        $this->assertEquals($expectedDescribedTable, $this->entityMetadata->getFields($this->model));
        //get from cached
        $this->connection->expects($this->never())->method('describeTable');
        $this->assertEquals($expectedDescribedTable, $this->entityMetadata->getFields($this->model));
    }
}
