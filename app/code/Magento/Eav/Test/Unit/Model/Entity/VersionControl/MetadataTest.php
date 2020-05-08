<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\VersionControl;

use Magento\Eav\Model\Entity\VersionControl\Metadata;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for version control metadata model.
 */
class MetadataTest extends TestCase
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var AbstractModel|MockObject
     */
    protected $model;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->model = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(['getAttributes'])
            ->onlyMethods(['getResource'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getConnection', 'getEntityTable']
        );

        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false
        );

        $this->model->expects($this->any())->method('getResource')->willReturn($this->resource);

        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $this->metadata = $objectManager->getObject(
            Metadata::class
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
