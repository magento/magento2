<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\QueryModifierInterface;

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
     * @var QueryModifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryModifierMock;

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
        $this->queryModifierMock = $this->getMock(QueryModifierInterface::class);
        $this->fieldDataConverter = $objectManager->getObject(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverterMock
            ]
        );
    }

    /**
     * @param boolean $useQueryModifier
     * @param int $numQueryModifierCalls
     * @dataProvider convertDataProvider
     */
    public function testConvert($useQueryModifier, $numQueryModifierCalls)
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
        $this->queryModifierMock->expects($this->exactly($numQueryModifierCalls))
            ->method('modify')
            ->with($this->selectMock);
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
        $this->fieldDataConverter->convert(
            $this->connectionMock,
            $table,
            $identifier,
            $field,
            $useQueryModifier ? $this->queryModifierMock : null
        );
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            [false, 0],
            [true, 1]
        ];
    }
}
