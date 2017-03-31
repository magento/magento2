<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Analytics\ReportXml\Query;
use Magento\Analytics\ReportXml\QueryFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ReportValidatorTest
 */
class ReportValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var QueryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryFactoryMock;

    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->connectionFactoryMock = $this->getMockBuilder(ConnectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->queryFactoryMock = $this->getMockBuilder(QueryFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->queryMock = $this->getMockBuilder(Query::class)->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $this->selectMock = $this->getMockBuilder(Select::class)->disableOriginalConstructor()
            ->getMock();
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
     * @param \PHPUnit_Framework_MockObject_Stub $queryReturnStub
     */
    public function testValidate($reportName, $result, \PHPUnit_Framework_MockObject_Stub $queryReturnStub)
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
