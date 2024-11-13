<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

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

        $this->selectMock->expects($this->once())
            ->method('limit')
            ->with(ReportProvider::BATCH_SIZE, 0)
            ->willReturn($this->selectMock);

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

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->willReturn(5);

        $this->iteratorFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->statementMock, null)
            ->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->subject->getBatchReport($reportName));
    }
}
