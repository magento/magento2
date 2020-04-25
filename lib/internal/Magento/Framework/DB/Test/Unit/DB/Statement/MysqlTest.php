<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\DB\Statement;

use Magento\Framework\DB\Statement\Parameter;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 */
class MysqlTest extends TestCase
{
    /**
     * @var \Zend_Db_Adapter_Abstract|MockObject
     */
    private $adapterMock;

    /**
     * @var \PDO|MockObject
     */
    private $pdoMock;

    /**
     * @var \Zend_Db_Profiler|MockObject
     */
    private $zendDbProfilerMock;

    /**
     * @var \PDOStatement|MockObject
     */
    private $pdoStatementMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->getMockForAbstractClass(
            \Zend_Db_Adapter_Abstract::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getProfiler']
        );
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->adapterMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->pdoMock);
        $this->zendDbProfilerMock = $this->createMock(\Zend_Db_Profiler::class);
        $this->adapterMock->expects($this->once())
            ->method('getProfiler')
            ->willReturn($this->zendDbProfilerMock);
        $this->pdoStatementMock = $this->createMock(\PDOStatement::class);
    }

    public function testExecuteWithoutParams()
    {
        $query = 'SET @a=1;';
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->expects($this->once())
            ->method('execute');
        (new Mysql($this->adapterMock, $query))->_execute();
    }

    public function testExecuteWhenThrowPDOException()
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);
        $this->expectExceptionMessage('test message, query was:');
        $errorReporting = error_reporting();
        $query = 'SET @a=1;';
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new \PDOException('test message'));

        $this->assertEquals($errorReporting, error_reporting(), 'Error report level was\'t restored');

        (new Mysql($this->adapterMock, $query))->_execute();
    }

    public function testExecuteWhenParamsAsPrimitives()
    {
        $params = [':param1' => 'value1', ':param2' => 'value2'];
        $query = 'UPDATE `some_table1` SET `col1`=\'val1\' WHERE `param1`=\':param1\' AND `param2`=\':param2\';';
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->expects($this->never())
            ->method('bindParam');
        $this->pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with($params);

        (new Mysql($this->adapterMock, $query))->_execute($params);
    }

    public function testExecuteWhenParamsAsParameterObject()
    {
        $param1 = $this->createMock(Parameter::class);
        $param1Value = 'SomeValue';
        $param1DataType = 'dataType';
        $param1Length = '9';
        $param1DriverOptions = 'some driver options';
        $param1->expects($this->once())
            ->method('getIsBlob')
            ->willReturn(false);
        $param1->expects($this->once())
            ->method('getDataType')
            ->willReturn($param1DataType);
        $param1->expects($this->once())
            ->method('getLength')
            ->willReturn($param1Length);
        $param1->expects($this->once())
            ->method('getDriverOptions')
            ->willReturn($param1DriverOptions);
        $param1->expects($this->once())
            ->method('getValue')
            ->willReturn($param1Value);
        $params = [
            ':param1' => $param1,
            ':param2' => 'value2',
        ];
        $query = 'UPDATE `some_table1` SET `col1`=\'val1\' WHERE `param1`=\':param1\' AND `param2`=\':param2\';';
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->expects($this->exactly(2))
            ->method('bindParam')
            ->withConsecutive(
                [':param1', $param1Value, $param1DataType, $param1Length, $param1DriverOptions],
                [':param2', 'value2', \PDO::PARAM_STR, null, null]
            );
        $this->pdoStatementMock->expects($this->once())
            ->method('execute');

        (new Mysql($this->adapterMock, $query))->_execute($params);
    }
}
