<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\SelectFactory;
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
     * @var SelectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectFactoryMock;

    /**
     * @var FieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->connectionMock = $this->getMock(AdapterInterface::class);
        $this->queryGeneratorMock = $this->getMock(Generator::class, [], [], '', false);
        $this->dataConverterMock = $this->getMock(DataConverterInterface::class);
        $this->selectMock = $this->getMock(Select::class, [], [], '', false);
        $this->queryModifierMock = $this->getMock(QueryModifierInterface::class);
        $this->selectFactoryMock = $this->getMockBuilder(SelectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldDataConverter = $this->objectManager->getObject(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverterMock,
                'selectFactory' => $this->selectFactoryMock,
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
        $rows = [1 => 'value'];
        $convertedValue = 'converted value';
        $this->selectFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->connectionMock)
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
            ->method('fetchPairs')
            ->with($iterator[0])
            ->willReturn($rows);
        $this->dataConverterMock->expects($this->once())
            ->method('convert')
            ->with($rows[1])
            ->willReturn($convertedValue);
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $table,
                [$field => $convertedValue],
                [$identifier . ' IN (?)' => [1]]
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
            [false, 0, ],
            [true, 1]
        ];
    }

    /**
     * @param null|int $envBatchSize
     * @dataProvider convertBatchSizeFromEnvDataProvider
     */
    public function testConvertBatchSizeFromEnv($envBatchSize, $usedBatchSize)
    {
        $table = 'table';
        $identifier = 'id';
        $field = 'field';
        $where = $field . ' IS NOT NULL';
        $this->selectFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->connectionMock)
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
            ->with($identifier, $this->selectMock, $usedBatchSize)
            ->willReturn([]);
        $fieldDataConverter = $this->objectManager->getObject(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverterMock,
                'selectFactory' => $this->selectFactoryMock,
                'envBatchSize' => $envBatchSize
            ]
        );
        $fieldDataConverter->convert(
            $this->connectionMock,
            $table,
            $identifier,
            $field
        );
    }

    /**
     * @return array
     */
    public function convertBatchSizeFromEnvDataProvider()
    {
        return [
            [null, FieldDataConverter::DEFAULT_BATCH_SIZE],
            [100000, 100000],
        ];
    }

    /**
     * @param string|int $batchSize
     * @expectedException \InvalidArgumentException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage Invalid value for environment variable DATA_CONVERTER_BATCH_SIZE. Should be integer, >= 1 and < value of PHP_INT_MAX
     * @codingStandardsIgnoreEnd
     * @dataProvider convertBatchSizeFromEnvInvalidDataProvider
     */
    public function testConvertBatchSizeFromEnvInvalid($batchSize)
    {
        $table = 'table';
        $identifier = 'id';
        $field = 'field';
        $where = $field . ' IS NOT NULL';
        $this->selectFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->connectionMock)
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
        $fieldDataConverter = $this->objectManager->getObject(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverterMock,
                'selectFactory' => $this->selectFactoryMock,
                'envBatchSize' => $batchSize
            ]
        );
        $fieldDataConverter->convert(
            $this->connectionMock,
            $table,
            $identifier,
            $field
        );
    }

    /**
     * @return array
     */
    public function convertBatchSizeFromEnvInvalidDataProvider()
    {
        return [
            ['value'],
            [bcadd(PHP_INT_MAX, 1)],
        ];
    }
}
