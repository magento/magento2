<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Analytics\ReportXml\Query;
use Magento\Analytics\ReportXml\QueryFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;

class ReportValidatorTest extends TestCase
{
    /**
     * @var ConnectionFactory|MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var QueryFactory|MockObject
     */
    private $queryFactoryMock;

    /**
     * @var Query|MockObject
     */
    private $queryMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ReportValidator
     */
    private $reportValidator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->connectionFactoryMock = $this->createMock(ConnectionFactory::class);
        $this->queryFactoryMock = $this->createMock(QueryFactory::class);
        $this->queryMock = $this->createMock(Query::class);
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->selectMock = $this->createMock(Select::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->reportValidator = $this->objectManagerHelper->getObject(
            ReportValidator::class,
            [
                'connectionFactory' => $this->connectionFactoryMock,
                'queryFactory' => $this->queryFactoryMock
            ]
        );
    }

    /**
     * @dataProvider errorDataProvider
     * @param string $reportName
     * @param array $result
     * @param Stub $queryReturnStub
     */
    public function testValidate($reportName, $result, Stub $queryReturnStub)
    {
        $connectionName = 'testConnection';
        $this->queryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->queryMock);
        $this->queryMock->expects($this->once())->method('getConnectionName')->willReturn($connectionName);
        $this->connectionFactoryMock->expects($this->once())->method('getConnection')
            ->with($connectionName)
            ->willReturn($this->connectionMock);
        $this->queryMock->expects($this->atLeastOnce())->method('getSelect')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('limit')->with(0);
        $this->connectionMock->expects($this->once())->method('query')->with($this->selectMock)->will($queryReturnStub);
        $this->assertEquals($result, $this->reportValidator->validate($reportName));
    }

    /**
     * Provide variations of the error returning
     *
     * @return array
     */
    public function errorDataProvider()
    {
        $reportName = 'test';
        $errorMessage = 'SQL Error 42';
        return [
            [
                $reportName,
                'expectedResult' => [],
                'queryReturnStub' => $this->returnValue(null)
            ],
            [
                $reportName,
                'expectedResult' => [$reportName, $errorMessage],
                'queryReturnStub' => $this->throwException(new \Zend_Db_Statement_Exception($errorMessage))
            ]
        ];
    }
}
