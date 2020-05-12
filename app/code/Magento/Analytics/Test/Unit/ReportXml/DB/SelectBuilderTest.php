<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectBuilderTest extends TestCase
{
    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->selectMock = $this->createMock(Select::class);

        $this->selectBuilder = new SelectBuilder($this->resourceConnectionMock);
    }

    public function testCreate()
    {
        $connectionName = 'MySql';
        $from = ['customer c'];
        $columns = ['id', 'name', 'price'];
        $filter = 'filter';
        $joins = [
            ['link-type' => 'left', 'table' => 'customer', 'condition' => 'in'],
            ['link-type' => 'inner', 'table' => 'price', 'condition' => 'eq'],
            ['link-type' => 'right', 'table' => 'attribute', 'condition' => 'neq'],
        ];
        $groups = ['id', 'name'];
        $this->selectBuilder->setConnectionName($connectionName)
            ->setFrom($from)
            ->setColumns($columns)
            ->setFilters([$filter])
            ->setJoins($joins)
            ->setGroup($groups);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with($connectionName)
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($from, []);
        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with($columns);
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with($filter);
        $this->selectMock->expects($this->once())
            ->method('joinLeft')
            ->with($joins[0]['table'], $joins[0]['condition'], []);
        $this->selectMock->expects($this->once())
            ->method('joinInner')
            ->with($joins[1]['table'], $joins[1]['condition'], []);
        $this->selectMock->expects($this->once())
            ->method('joinRight')
            ->with($joins[2]['table'], $joins[2]['condition'], []);
        $this->selectBuilder->create();
    }
}
