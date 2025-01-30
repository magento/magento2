<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogInterface;
use Magento\Framework\Mview\View\ChangelogBatchWalker;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsTableBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Coverage for Changelog View.
 *
 * @see \Magento\Framework\Mview\View\Changelog
 */
class ChangelogBatchWalkerTest extends TestCase
{
    /**
     * @var ChangelogBatchWalker
     */
    protected $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var Generator|MockObject
     */
    private $generator;

    /**
     * @var IdsTableBuilderInterface|MockObject
     */
    private $idsTableBuilder;

    /**
     * @var IdsSelectBuilderInterface|MockObject
     */
    private $idsSelectBuilder;

    /**
     * @var IdsContext|MockObject
     */
    private $idsContext;

    /**
     * @var ChangelogInterface
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
     * @var Select|MockObject
     */
    private $select;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generator = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->idsTableBuilder = $this->getMockBuilder(IdsTableBuilderInterface::class)
            ->getMockForAbstractClass();
        $this->idsSelectBuilder = $this->getMockBuilder(IdsSelectBuilderInterface::class)
            ->getMockForAbstractClass();
        $this->idsContext = $this->getMockBuilder(IdsContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->idsContext->expects($this->any())
            ->method('getSelectBuilder')
            ->willReturn($this->idsSelectBuilder);
        $this->idsContext->expects($this->any())
            ->method('getTableBuilder')
            ->willReturn($this->idsTableBuilder);

        $this->changeLog = $this->getMockBuilder(ChangelogInterface::class)
            ->getMockForAbstractClass();
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->select->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();
        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $this->model = new ChangelogBatchWalker(
            $this->resourceConnection,
            $this->generator,
            $this->idsContext
        );
    }

    public function testNoTemporaryTablesUsed()
    {
        $this->connection->expects($this->once())
            ->method('isTableExists')
            ->willReturn(true);
        $this->table->expects($this->any())
            ->method('getColumns')
            ->willReturn([]);
        $this->idsTableBuilder->expects($this->any())
            ->method('build')
            ->willReturn($this->table);
        $this->idsSelectBuilder->expects($this->any())
            ->method('build')
            ->willReturn($this->select);
        $this->generator->expects($this->any())
            ->method('generate')
            ->willReturn([]);

        foreach ($this->model->walk($this->changeLog, 1, 2, 1) as $iteration) {
            $this->assertEmpty($iteration);
            $this->connection->expects($this->once())
                ->method('createTable');
            $this->connection->expects($this->never())
                ->method('createTemporaryTableTable');
        }
    }
}
