<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

/**
 * A unit test for testing of the reports provider.
 */
class ReportProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Analytics\ReportXml\ReportProvider
     */
    private $subject;

    /**
     * @var \Magento\Analytics\ReportXml\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectMock;

    /**
     * @var \IteratorIterator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $iteratorMock;

    /**
     * @var \Magento\Framework\DB\Statement\Pdo\Mysql|\PHPUnit\Framework\MockObject\MockObject
     */
    private $statementMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Analytics\ReportXml\QueryFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Analytics\ReportXml\ConnectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var \Magento\Analytics\ReportXml\IteratorFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $iteratorFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->selectMock = $this->getMockBuilder(
            \Magento\Framework\DB\Select::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->queryMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\Query::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->queryMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->selectMock);

        $this->iteratorMock = $this->getMockBuilder(
            \IteratorIterator::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->statementMock = $this->getMockBuilder(
            \Magento\Framework\DB\Statement\Pdo\Mysql::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->statementMock->expects($this->any())
            ->method('getIterator')
            ->willReturn($this->iteratorMock);

        $this->connectionMock = $this->getMockBuilder(
            \Magento\Framework\DB\Adapter\AdapterInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->queryFactoryMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\QueryFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->iteratorFactoryMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\IteratorFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->iteratorMock = $this->getMockBuilder(
            \IteratorIterator::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->connectionFactoryMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\ConnectionFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\ReportXml\ReportProvider::class,
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
}
