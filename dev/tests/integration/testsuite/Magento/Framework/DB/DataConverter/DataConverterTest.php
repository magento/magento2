<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\DataConverter;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\QueryModifierInterface;
use Magento\Framework\DB\Select\InQueryModifier;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Query\BatchIterator;
use Magento\Framework\ObjectManagerInterface;

class DataConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InQueryModifier|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryModifierMock;

    /**
     * @var SerializedToJson
     */
    private $dataConverter;

    /**
     * @var BatchIterator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $iteratorMock;

    /**
     * @var Generator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryGeneratorMock;

    /**
     * @var Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectByRangeMock;

    /**
     * @var Mysql|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterMock;

    /**
     * @var FieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var InQueryModifier $queryModifier */
        $this->queryModifierMock = $this->getMockBuilder(QueryModifierInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['modify'])
            ->getMockForAbstractClass();

        $this->dataConverter = $this->objectManager->get(SerializedToJson::class);

        $this->iteratorMock = $this->getMockBuilder(BatchIterator::class)
            ->disableOriginalConstructor()
            ->setMethods(['current', 'valid', 'next'])
            ->getMock();

        $iterationComplete = false;

        // mock valid() call so iterator passes only current() result in foreach invocation
        $this->iteratorMock->expects($this->any())
            ->method('valid')
            ->willReturnCallback(
                function () use (&$iterationComplete) {
                    if (!$iterationComplete) {
                        $iterationComplete = true;
                        return true;
                    } else {
                        return false;
                    }
                }
            );

        $this->queryGeneratorMock = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        $this->selectByRangeMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->queryGeneratorMock->expects($this->any())
            ->method('generate')
            ->willReturn($this->iteratorMock);

        // mocking only current as next() is not supposed to be called
        $this->iteratorMock->expects($this->any())
            ->method('current')
            ->willReturn($this->selectByRangeMock);

        $this->adapterMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchPairs', 'quoteInto', 'update'])
            ->getMock();

        $this->adapterMock->expects($this->any())
            ->method('quoteInto')
            ->willReturn('field=value');

        $this->fieldDataConverter = $this->objectManager->create(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverter
            ]
        );
    }

    /**
     * Test that exception with valid text is thrown when data is corrupted
     *
     */
    public function testDataConvertErrorReporting()
    {
        $this->expectException(\Magento\Framework\DB\FieldDataConversionException::class);
        $this->expectExceptionMessage('Error converting field `value` in table `table` where `id`=2 using');

        $rows = [
            1 => 'N;',
            2 => 'a:2:{s:3:"foo";s:3:"bar";s:3:"bar";s:',
        ];

        $this->adapterMock->expects($this->any())
            ->method('fetchPairs')
            ->with($this->selectByRangeMock)
            ->willReturn($rows);

        $this->adapterMock->expects($this->once())
            ->method('update')
            ->with('table', ['value' => 'null'], ['id IN (?)' => [1]]);

        $this->fieldDataConverter->convert($this->adapterMock, 'table', 'id', 'value', $this->queryModifierMock);
    }

    public function testAlreadyConvertedDataSkipped()
    {
        $rows = [
            2 => '[]',
            3 => '{}',
            4 => 'null',
            5 => '""',
            6 => '0',
            7 => 'N;',
            8 => '{"valid": "json value"}',
        ];

        $this->adapterMock->expects($this->any())
            ->method('fetchPairs')
            ->with($this->selectByRangeMock)
            ->willReturn($rows);

        $this->adapterMock->expects($this->once())
            ->method('update')
            ->with('table', ['value' => 'null'], ['id IN (?)' => [7]]);

        $this->fieldDataConverter->convert($this->adapterMock, 'table', 'id', 'value', $this->queryModifierMock);
    }
}
