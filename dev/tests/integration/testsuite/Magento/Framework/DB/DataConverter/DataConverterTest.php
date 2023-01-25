<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\DataConverter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\FieldDataConversionException;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\QueryModifierInterface;
use Magento\Framework\DB\Select\InQueryModifier;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Query\BatchIterator;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataConverterTest extends TestCase
{
    /**
     * @var InQueryModifier|MockObject
     */
    private $queryModifierMock;

    /**
     * @var SerializedToJson
     */
    private $dataConverter;

    /**
     * @var BatchIterator|MockObject
     */
    private $iteratorMock;

    /**
     * @var Generator|MockObject
     */
    private $queryGeneratorMock;

    /**
     * @var Select|MockObject
     */
    private $selectByRangeMock;

    /**
     * @var Mysql|MockObject
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
            ->setMethods(['fetchPairs', 'fetchAll', 'quoteInto', 'update', 'prepareSqlCondition'])
            ->getMock();

        $this->adapterMock->expects($this->any())
            ->method('quoteInto')
            ->willReturn('field=value');

        $batchIteratorFactory = $this->createMock(\Magento\Framework\DB\Query\BatchRangeIteratorFactory::class);
        $batchIteratorFactory->method('create')->willReturn($this->iteratorMock);

        $this->fieldDataConverter = $this->objectManager->create(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverter,
                'batchIteratorFactory' => $batchIteratorFactory,
            ]
        );
    }

    /**
     * Test that exception with valid text is thrown when data is corrupted
     *
     */
    public function testDataConvertErrorReporting()
    {
        $this->expectException(FieldDataConversionException::class);
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

    public function testAlreadyConvertedDataSkippedWithCompositeIdentifier(): void
    {
        $rows = [
            [
                'key_one' => 1,
                'key_two' => 1,
                'value' => '[]',
            ],
            [
                'key_one' => 1,
                'key_two' => 2,
                'value' => '{}',
            ],
            [
                'key_one' => 3,
                'key_two' => 3,
                'value' => 'N;',
            ],
            [
                'key_one' => 4,
                'key_two' => 1,
                'value' => '{"valid": "json value"}',
            ]
        ];

        $this->adapterMock->expects($this->any())
            ->method('prepareSqlCondition')
            ->willReturnCallback(
                function ($column, $value) {
                    return "$column = $value";
                }
            );

        $this->adapterMock->expects($this->any())
            ->method('fetchAll')
            ->with($this->selectByRangeMock)
            ->willReturn($rows);

        $this->adapterMock->expects($this->once())
            ->method('update')
            ->with('table', ['value' => 'null'], 'key_one = 3 AND key_two = 3');

        $this->fieldDataConverter->convert($this->adapterMock, 'table', 'id1,id2', 'value', $this->queryModifierMock);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture createFixtureTable
     */
    public function testTableWithCompositeIdentifier(): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $tableName = 'test_fixture_table';
        $keyOneValues = range(1, 9);
        $keyTwoValues = [3, 6, 9];
        $records = [];
        foreach ($keyOneValues as $keyOneValue) {
            foreach (array_slice($keyTwoValues, 0, rand(1, 3)) as $keyTwoValue) {
                $records[] = [
                    'key_one' => $keyOneValue,
                    'key_two' => $keyTwoValue,
                    // phpcs:ignore
                    'value' => serialize(['key_one' => $keyOneValue, 'key_two' => $keyTwoValue]),
                ];
            }
        }
        // phpcs:ignore
        $repeatedVal = serialize([]);
        $records[] = [
            'key_one' => 10,
            'key_two' => 3,
            'value' => $repeatedVal,
        ];
        $records[] = [
            'key_one' => 10,
            'key_two' => 6,
            'value' => $repeatedVal,
        ];
        $records[] = [
            'key_one' => 11,
            'key_two' => 6,
            'value' => $repeatedVal,
        ];

        $resource->getConnection()->insertMultiple($tableName, $records);

        $expected = [];

        foreach ($records as $record) {
            $record['value'] = $this->dataConverter->convert($record['value']);
            $expected[] = $record;
        }

        $batchSize = 5;
        $fieldDataConverter = $this->objectManager->create(
            FieldDataConverter::class,
            [
                'dataConverter' => $this->dataConverter,
                'envBatchSize' => $batchSize
            ]
        );
        $fieldDataConverter->convert($resource->getConnection(), $tableName, 'key_one,key_two', 'value');
        $actual = $resource->getConnection()->fetchAll(
            $resource->getConnection()->select()->from($tableName)
        );
        $this->assertEquals($expected, $actual, json_encode($records));
    }

    public static function createFixtureTable(): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $tableName = 'test_fixture_table';
        $table = $resource->getConnection()
            ->newTable(
                $tableName
            )
            ->addColumn(
                'key_one',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'key_two',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addIndex(
                $tableName . '_index_key_one_key_two',
                [
                    'key_one',
                    'key_two',
                ],
                [
                    'type' => AdapterInterface::INDEX_TYPE_PRIMARY
                ]
            );
        $resource->getConnection()->createTable($table);
    }

    public static function createFixtureTableRollback(): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $tableName = 'test_fixture_table';
        $resource->getConnection()->dropTable($tableName);
    }
}
