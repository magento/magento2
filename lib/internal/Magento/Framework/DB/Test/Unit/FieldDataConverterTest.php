<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\QueryModifierInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldDataConverterTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Generator|MockObject
     */
    private $queryGeneratorMock;

    /**
     * @var DataConverterInterface|MockObject
     */
    private $dataConverterMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var QueryModifierInterface|MockObject
     */
    private $queryModifierMock;

    /**
     * @var SelectFactory|MockObject
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

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->queryGeneratorMock = $this->createMock(Generator::class);
        $this->dataConverterMock = $this->getMockForAbstractClass(DataConverterInterface::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->queryModifierMock = $this->getMockForAbstractClass(QueryModifierInterface::class);
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
    public static function convertDataProvider()
    {
        return [
            [false, 0],
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
    public static function convertBatchSizeFromEnvDataProvider()
    {
        return [
            [null, FieldDataConverter::DEFAULT_BATCH_SIZE],
            [100000, 100000],
        ];
    }

    /**
     * @param string|int $batchSize
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     * @dataProvider convertBatchSizeFromEnvInvalidDataProvider
     */
    public function testConvertBatchSizeFromEnvInvalid($batchSize)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Invalid value for environment variable DATA_CONVERTER_BATCH_SIZE. '
            . 'Should be integer, >= 1 and < value of PHP_INT_MAX'
        );
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
    public static function convertBatchSizeFromEnvInvalidDataProvider()
    {
        return [
            ['value'],
            [bcadd((string)PHP_INT_MAX, (string)1)],
        ];
    }
}
