<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\Select;

class FieldDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryGeneratorMock;

    /**
     * @var DataConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConverterMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var FieldDataConverter
     */
    private $fieldDataConverter;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->connectionMock = $this->getMock(AdapterInterface::class);
        $this->queryGeneratorMock = $this->getMock(Generator::class, [], [], '', false);
        $this->dataConverterMock = $this->getMock(DataConverterInterface::class);
        $this->selectMock = $this->getMock(Select::class, [], [], '', false);
        $this->fieldDataConverter = $objectManager->getObject(
            FieldDataConverter::class,
            [
                'connection' => $this->connectionMock,
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverterMock
            ]
        );
    }

    public function testConvert()
    {
        $table = 'table';
        $identifier = 'id';
        $field = 'field';
        $where = $field . ' IS NOT NULL';
        $iterator = ['query 1'];
        $rows = [
            [
                $identifier => 1,
                $field => 'value'
            ]
        ];
        $convertedValue = 'converted value';
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('from')
            ->with(
                $table,
                [$identifier, $field]
            )
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with($where)
            ->willReturnSelf();
        $this->queryGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($identifier, $this->selectMock)
            ->willReturn($iterator);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($iterator[0])
            ->willReturn($rows);
        $this->dataConverterMock->expects($this->once())
            ->method('convert')
            ->with($rows[0][$field])
            ->willReturn($convertedValue);
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $table,
                [$field => $convertedValue],
                [$identifier . ' = ?' => $rows[0][$identifier]]
            );
        $this->fieldDataConverter->convert($table, $identifier, $field);
    }
}
