<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\IteratorFactory;
use Magento\Analytics\ReportXml\Query;
use Magento\Analytics\ReportXml\QueryFactory;
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the reports provider.
 */
class ReportProviderTest extends TestCase
{
    /**
     * @var ReportProvider
     */
    private $subject;

    /**
     * @var Query|MockObject
     */
    private $queryMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var \IteratorIterator|MockObject
     */
    private $iteratorMock;

    /**
     * @var Mysql|MockObject
     */
    private $statementMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var QueryFactory|MockObject
     */
    private $queryFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ConnectionFactory|MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var IteratorFactory|MockObject
     */
    private $iteratorFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->selectMock = $this->createMock(Select::class);

        $this->queryMock = $this->createMock(Query::class);
        $this->queryMock
            ->method('getSelect')
            ->willReturn($this->selectMock);

        $this->iteratorMock = $this->createMock(\IteratorIterator::class);

        $this->statementMock = $this->createMock(Mysql::class);
        $this->statementMock
            ->method('getIterator')
            ->willReturn($this->iteratorMock);

        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->queryFactoryMock = $this->createMock(QueryFactory::class);

        $this->iteratorFactoryMock = $this->createMock(IteratorFactory::class);
        $this->iteratorMock = $this->createMock(\IteratorIterator::class);
        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->connectionFactoryMock = $this->createMock(ConnectionFactory::class);

        $this->subject = $this->objectManagerHelper->getObject(
            ReportProvider::class,
            [
                'queryFactory' => $this->queryFactoryMock,
                'connectionFactory' => $this->connectionFactoryMock,
                'iteratorFactory' => $this->iteratorFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetReport()
    {
        $reportName = 'test_report';
        $connectionName = 'sales';

        $this->queryFactoryMock->expects($this->once())
            ->method('create')
            ->with($reportName)
            ->willReturn($this->queryMock);

        $this->connectionFactoryMock->expects($this->once())
            ->method('getConnection')
            ->with($connectionName)
            ->willReturn($this->connectionMock);

        $this->queryMock->expects($this->once())
            ->method('getConnectionName')
            ->willReturn($connectionName);

        $this->queryMock->expects($this->once())
            ->method('getConfig')
            ->willReturn(
                [
                    'connection' => $connectionName
                ]
            );

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($this->statementMock);

        $this->iteratorFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->statementMock, null)
            ->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->subject->getReport($reportName));
    }

    /**
     * @return void
     */
    public function testGetBatchReport()
    {
        $reportName = 'test_report';
        $connectionName = 'sales';
        $tableName = 'sales_order_item';
        $cursorColumn = 'item_id';
        $this->queryFactoryMock->expects($this->once())
            ->method('create')
            ->with($reportName)
            ->willReturn($this->queryMock);

        $this->connectionFactoryMock->expects($this->once())
            ->method('getConnection')
            ->with($connectionName)
            ->willReturn($this->connectionMock);

        $this->queryMock->expects($this->once())
            ->method('getConnectionName')
            ->willReturn($connectionName);
        $this->queryMock->expects($this->atLeast(2))
            ->method('getConfig')
            ->willReturn(
                [
                    'name' => $reportName,
                    'connection' => $connectionName,
                    'source' => ['name' => $tableName]
                ]
            );
        $this->queryMock->expects($this->once())->method('getSelect')->willReturn($this->selectMock);
        $this->selectMock->method('where')->willReturn($this->selectMock);
        $this->selectMock->method('order')->willReturn($this->selectMock);
        $this->selectMock->method('limit')->willReturn($this->selectMock);
        $rows = [
            [$cursorColumn => 1, 'other_field' => 'value1'],
            [$cursorColumn => 2, 'other_field' => 'value2'],
            [$cursorColumn => 3, 'other_field' => 'value3']
        ];
        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf(Select::class))
            ->willReturn($this->statementMock);
        $this->statementMock->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($rows);
        $this->iteratorFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(\ArrayIterator::class), null)
            ->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->subject->getBatchReport($reportName));
    }

    /**
     * @return void
     */
    public function testGetBatchReportWithOffsetFallback()
    {
        $reportName = 'test_report';
        $connectionName = 'sales';
        $tableName = 'unknown_table';
        $this->queryFactoryMock->expects($this->once())
            ->method('create')
            ->with($reportName)
            ->willReturn($this->queryMock);
        $this->connectionFactoryMock->expects($this->once())
            ->method('getConnection')
            ->with($connectionName)
            ->willReturn($this->connectionMock);
        $this->queryMock->expects($this->once())
            ->method('getConnectionName')
            ->willReturn($connectionName);
        $this->queryMock->expects($this->atLeast(2))
            ->method('getConfig')
            ->willReturn(
                [
                    'name' => $reportName,
                    'connection' => $connectionName,
                    'source' => ['name' => $tableName]
                ]
            );
        $countSelectMock = $this->createMock(Select::class);
        $this->queryMock->expects($this->once())->method('getSelectCountSql')->willReturn($countSelectMock);
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($countSelectMock)
            ->willReturn(100);
        $this->queryMock->expects($this->once())->method('getSelect')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('limit')
            ->with(ReportProvider::BATCH_SIZE, 0)
            ->willReturn($this->selectMock);
        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($this->statementMock);
        $this->iteratorFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->statementMock, null)
            ->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->subject->getBatchReport($reportName));
    }
}
