<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\VersionControl;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** Control MetadataTest
 */
class MetadataTest extends TestCase
{
    /**
     * @var Metadata
     */
    protected $entityMetadata;

    /**
     * @var MockObject|AbstractModel
     */
    protected $model;

    /**
     * @var MockObject|AbstractDb
     */
    protected $resource;

    /**
     * @var MockObject|AdapterInterface
     */
    protected $connection;

    /**
     * Initialization
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $this->createMock(AbstractModel::class);
        $this->resource = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            "",
            false,
            false,
            true,
            ['getConnection', 'getMainTable']
        );
        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            "",
            false,
            false
        );
        $this->model->expects($this->any())->method('getResource')->willReturn($this->resource);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->entityMetadata = $objectManager->getObject(
            Metadata::class
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
