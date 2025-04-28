<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Mview\View\ChangelogBatchWalker;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsTableBuilder;
use Magento\Framework\Mview\View\ChangelogInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdsTableBuilderTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var ChangelogInterface|MockObject
     */
    private $changeLog;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Table|MockObject
     */
    private $table;

    /**
     * @var IdsTableBuilder
     */
    private $model;

    protected function setUp(): void
    {
        $this->changeLog = $this->getMockBuilder(ChangelogInterface::class)
            ->getMockForAbstractClass();
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->any())
            ->method('newTable')
            ->willReturn($this->table);

        $this->model = new IdsTableBuilder($this->resourceConnection);
    }

    public function testBuildDoNotCreateMemoryTable() : void
    {
        $this->table->expects($this->never())
            ->method('setOption')
            ->with('type', 'memory');

        $this->model->build($this->changeLog);
    }
}
