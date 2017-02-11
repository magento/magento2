<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Class SelectBuilderTest
 */
class SelectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $this->selectBuilder->setConnectionName($connectionName);
        $this->selectBuilder->setFrom($from);
        $this->selectBuilder->setColumns($columns);
        $this->selectBuilder->setFilters([$filter]);
        $this->selectBuilder->setJoins($joins);
        $this->selectBuilder->setGroup($groups);
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
