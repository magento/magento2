<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Adapter\Pdo;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql as PdoMysqlAdapter;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\SchemaListener;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\DB\Adapter\Pdo\Mysql class test
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MysqlTest extends TestCase
{
    public const CUSTOM_ERROR_HANDLER_MESSAGE = 'Custom error handler message';

    /**
     * @var SelectFactory|MockObject
     */
    protected $selectFactory;

    /**
     * @var SchemaListener|MockObject
     */
    private $schemaListenerMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var MockObject|\Zend_Db_Profiler
     */
    private $profiler;

    /**
     * @var \PDO|MockObject
     */
    private $connection;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->schemaListenerMock = $this->getMockBuilder(SchemaListener::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->profiler = $this->createMock(
            \Zend_Db_Profiler::class
        );
        $this->connection = $this->createMock(\PDO::class);
    }

    /**
     * @dataProvider bigintResultProvider
     */
    public function testPrepareColumnValueForBigint($value, $expectedResult)
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);
        $result = $adapter->prepareColumnValue(
            ['DATA_TYPE' => 'bigint'],
            $value
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data Provider for testPrepareColumnValueForBigint
     */
    public static function bigintResultProvider()
    {
        return [
            [1, 1],
            [0, 0],
            [-1, -1],
            [1.0, 1],
            [0.0, 0],
            [-1.0, -1],
            [1e-10, 0],
            [7.9, 8],
            [PHP_INT_MAX, PHP_INT_MAX],
            [2147483647 + 1, '2147483648'],
            [9223372036854775807 + 1, '9223372036854775808'],
            [9223372036854775807, '9223372036854775807'],
            [9223372036854775807.3423424234, '9223372036854775808'],
            [2147483647 * pow(10, 10)+12, '21474836470000001024'],
            [9223372036854775807 * pow(10, 10)+12, '92233720368547758080000000000'],
            [(0.099999999999999999999999995+0.2+0.3+0.4+0.5)*10, '15'],
            ['21474836470000000012', '21474836470000001024'],
            [0x5468792130ABCDEF, '6082244480221302255'],
        ];
    }

    /**
     * Test not DDL query inside transaction
     *
     * @dataProvider sqlQueryProvider
     */
    public function testCheckNotDdlTransaction($query)
    {
        $mockAdapter = $this->getMysqlPdoAdapterMockForDdlQueryTest();
        try {
            $mockAdapter->query($query);
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                $e->getMessage(),
                AdapterInterface::ERROR_DDL_MESSAGE
            );
        }

        $select = new Select($mockAdapter, new SelectRenderer([]));
        $select->from('user');
        try {
            $mockAdapter->query($select);
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                $e->getMessage(),
                AdapterInterface::ERROR_DDL_MESSAGE
            );
        }
    }

    /**
     * Test DDL query inside transaction in Developer mode
     *
     * @dataProvider ddlSqlQueryProvider
     */
    public function testCheckDdlTransaction($ddlQuery)
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('DDL statements are not allowed in transactions');
        $this->getMysqlPdoAdapterMockForDdlQueryTest()->query($ddlQuery);
    }

    public function testMultipleQueryException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Multiple queries can\'t be executed. Run a single query and try again.');
        $sql = "SELECT COUNT(*) AS _num FROM test; ";
        $sql .= "INSERT INTO test(id) VALUES (1); ";
        $sql .= "SELECT COUNT(*) AS _num FROM test; ";
        $this->getMysqlPdoAdapterMockForDdlQueryTest()->query($sql);
    }

    /**
     * Data Provider for testCheckDdlTransaction
     */
    public static function ddlSqlQueryProvider()
    {
        return [
            ['CREATE table user sasdasd'],
            ['ALTER table user'],
            ['TRUNCATE table user'],
            ['RENAME table user'],
            ['DROP table user'],
            ["\n\r  \t aLTeR  \t \n \r table \t\r\n\n user  "],
        ];
    }

    /**
     * Data Provider for testCheckNotDdlTransaction
     */
    public static function sqlQueryProvider()
    {
        return [
            ['SELECT * FROM user'],
            ['UPDATE user'],
            ['DELETE from user'],
            ['INSERT into user'],
        ];
    }

    /**
     * Test Asymmetric transaction rollback failure
     */
    public function testAsymmetricRollBackFailure()
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);
        $this->expectExceptionMessage(AdapterInterface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE);
        $adapter->rollBack();
    }

    /**
     * Test Asymmetric transaction commit failure
     */
    public function testAsymmetricCommitFailure()
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);
        $this->expectExceptionMessage(AdapterInterface::ERROR_ASYMMETRIC_COMMIT_MESSAGE);
        $adapter->commit();
    }

    /**
     * Test Asymmetric transaction commit success
     */
    public function testAsymmetricCommitSuccess()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect']);
        $this->addConnectionMock($adapter);
        $this->assertEquals(0, $adapter->getTransactionLevel());
        $adapter->beginTransaction();
        $this->assertEquals(1, $adapter->getTransactionLevel());
        $adapter->commit();
        $this->assertEquals(0, $adapter->getTransactionLevel());
    }

    /**
     * Test Asymmetric transaction rollback success
     */
    public function testAsymmetricRollBackSuccess()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect']);
        $this->addConnectionMock($adapter);
        $this->assertEquals(0, $adapter->getTransactionLevel());
        $adapter->beginTransaction();
        $this->assertEquals(1, $adapter->getTransactionLevel());
        $adapter->rollBack();
        $this->assertEquals(0, $adapter->getTransactionLevel());
    }

    /**
     * Test successful nested transaction
     */
    public function testNestedTransactionCommitSuccess()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect', '_beginTransaction', '_commit']);
        $adapter->expects($this->exactly(2))
            ->method('_connect');
        $adapter->expects($this->once())
            ->method('_beginTransaction');
        $adapter->expects($this->once())
            ->method('_commit');

        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $this->assertEquals(3, $adapter->getTransactionLevel());
        $adapter->commit();
        $adapter->commit();
        $adapter->commit();
        $this->assertEquals(0, $adapter->getTransactionLevel());
    }

    /**
     * Test successful nested transaction
     */
    public function testNestedTransactionRollBackSuccess()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect', '_beginTransaction', '_rollBack']);
        $adapter->expects($this->exactly(2))
            ->method('_connect');
        $adapter->expects($this->once())
            ->method('_beginTransaction');
        $adapter->expects($this->once())
            ->method('_rollBack');

        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $this->assertEquals(3, $adapter->getTransactionLevel());
        $adapter->rollBack();
        $adapter->rollBack();
        $adapter->rollBack();
        $this->assertEquals(0, $adapter->getTransactionLevel());
    }

    /**
     * Test successful nested transaction
     */
    public function testNestedTransactionLastRollBack()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect', '_beginTransaction', '_rollBack']);
        $adapter->expects($this->exactly(2))
            ->method('_connect');
        $adapter->expects($this->once())
            ->method('_beginTransaction');
        $adapter->expects($this->once())
            ->method('_rollBack');

        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $this->assertEquals(3, $adapter->getTransactionLevel());
        $adapter->commit();
        $adapter->commit();
        $adapter->rollBack();
        $this->assertEquals(0, $adapter->getTransactionLevel());
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     * phpcs:disable Magento2.Exceptions.ThrowCatch
     */
    public function testIncompleteRollBackFailureOnCommit()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect']);
        $this->addConnectionMock($adapter);

        try {
            $adapter->beginTransaction();
            $adapter->beginTransaction();
            $adapter->rollBack();
            $adapter->commit();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     * phpcs:disable Magento2.Exceptions.ThrowCatch
     */
    public function testIncompleteRollBackFailureOnBeginTransaction()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect']);
        $this->addConnectionMock($adapter);

        try {
            $adapter->beginTransaction();
            $adapter->beginTransaction();
            $adapter->rollBack();
            $adapter->beginTransaction();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testSequentialTransactionsSuccess()
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect', '_beginTransaction', '_rollBack', '_commit']);
        $this->addConnectionMock($adapter);

        $adapter->expects($this->exactly(4))
            ->method('_connect');
        $adapter->expects($this->exactly(2))
            ->method('_beginTransaction');
        $adapter->expects($this->once())
            ->method('_rollBack');
        $adapter->expects($this->once())
            ->method('_commit');

        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $adapter->beginTransaction();
        $adapter->rollBack();
        $adapter->rollBack();
        $adapter->rollBack();

        $adapter->beginTransaction();
        $adapter->commit();
    }

    /**
     * Test that column names are quoted in ON DUPLICATE KEY UPDATE section
     */
    public function testInsertOnDuplicateWithQuotedColumnName()
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);
        $table = 'some_table';
        $data = [
            'index' => 'indexValue',
            'row' => 'rowValue',
            'select' => 'selectValue',
            'insert' => 'insertValue',
        ];
        $fields = ['select', 'insert'];
        $sqlQuery = "INSERT  INTO `some_table` (`index`,`row`,`select`,`insert`) VALUES (?, ?, ?, ?) "
            . "ON DUPLICATE KEY UPDATE `select` = VALUES(`select`), `insert` = VALUES(`insert`)";

        $stmtMock = $this->createMock(\Zend_Db_Statement_Pdo::class);
        $bind = ['indexValue', 'rowValue', 'selectValue', 'insertValue'];
        $adapter->expects($this->once())
            ->method('query')
            ->with($sqlQuery, $bind)
            ->willReturn($stmtMock);

        $adapter->insertOnDuplicate($table, $data, $fields);
    }

    /**
     * @param array $options
     * @param string $expectedQuery
     *
     * @dataProvider addColumnDataProvider
     * @covers \Magento\Framework\DB\Adapter\Pdo\Mysql::addColumn
     * @covers \Magento\Framework\DB\Adapter\Pdo\Mysql::_getColumnDefinition
     */
    public function testAddColumn($options, $expectedQuery)
    {
        $adapter = $this->getMysqlPdoAdapterMock(
            ['tableColumnExists', '_getTableName', 'rawQuery', 'resetDdlCache', 'quote', 'getSchemaListener']
        );
        $adapter->expects($this->any())->method('getSchemaListener')->willReturn($this->schemaListenerMock);
        $adapter->expects($this->any())->method('_getTableName')->willReturnArgument(0);
        $adapter->expects($this->any())->method('quote')->willReturnOnConsecutiveCalls('', 'Some field');
        $adapter->expects($this->once())->method('rawQuery')->with($expectedQuery);
        $adapter->addColumn('tableName', 'columnName', $options);
    }

    /**
     * @return array
     */
    public static function addColumnDataProvider()
    {
        return [
            [
                'options' => [
                    'TYPE'        => 'integer',
                    'IDENTITY'    => true,
                    'UNSIGNED'    => true,
                    'NULLABLE'    => false,
                    'DEFAULT'     => null,
                    'COLUMN_NAME' => 'Some field',
                    'COMMENT'     => 'Some field',
                    'AFTER'       => 'Previous field',
                ],
                'expectedQuery' => 'ALTER TABLE `tableName` ADD COLUMN `columnName` int UNSIGNED '
                    . 'NOT NULL default  auto_increment COMMENT Some field AFTER `Previous field` ',
            ]
        ];
    }

    /**
     * @dataProvider getIndexNameDataProvider
     */
    public function testGetIndexName($name, $fields, $indexType, $expectedName)
    {
        $resultIndexName = $this->getMysqlPdoAdapterMockForDdlQueryTest()->getIndexName($name, $fields, $indexType);
        $this->assertStringStartsWith($expectedName, $resultIndexName);
    }

    /**
     * @return array
     */
    public static function getIndexNameDataProvider()
    {
        // 65 characters long - will be compressed
        $longTableName = '__________________________________________________long_table_name';
        return [
            [$longTableName, [], AdapterInterface::INDEX_TYPE_UNIQUE, 'UNQ_'],
            [$longTableName, [], AdapterInterface::INDEX_TYPE_FULLTEXT, 'FTI_'],
            [$longTableName, [], AdapterInterface::INDEX_TYPE_INDEX, 'IDX_'],
            ['short_table_name', ['field1', 'field2'], '', 'SHORT_TABLE_NAME_FIELD1_FIELD2'],
        ];
    }

    public function testConfigValidation()
    {
        $subject = (new ObjectManager($this))->getObject(
            Mysql::class,
            [
                'config' => ['host' => 'localhost'],
            ]
        );

        $this->assertInstanceOf(Mysql::class, $subject);
    }

    public function testConfigValidationByPortWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Port must be configured within host (like \'localhost:33390\') parameter, not within port'
        );
        (new ObjectManager($this))->getObject(
            Mysql::class,
            ['config' => ['host' => 'localhost', 'port' => '33390']]
        );
    }

    /**
     * @param string $indexName
     * @param string $indexType
     * @param array $keyLists
     * @param \Exception $exception
     * @param string $query
     * @throws \ReflectionException
     * @throws \Zend_Db_Exception
     * @dataProvider addIndexWithDuplicationsInDBDataProvider
     */
    public function testAddIndexWithDuplicationsInDB(
        string $indexName,
        string $indexType,
        array $keyLists,
        string $query,
        string $exceptionMessage,
        array $ids
    ) {
        $tableName = 'core_table';
        $fields = ['sku', 'field2'];
        $quotedFields = [$this->quoteIdentifier('sku'), $this->quoteIdentifier('field2')];

        $exception = new \Exception(
            sprintf(
                $exceptionMessage,
                $tableName,
                implode(',', $quotedFields)
            )
        );

        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        $adapter = $this->getMysqlPdoAdapterMock([
            'describeTable',
            'getIndexList',
            'quoteIdentifier',
            '_getTableName',
            'rawQuery',
            '_removeDuplicateEntry',
            'resetDdlCache',
        ]);
        $this->addConnectionMock($adapter);
        $columns = ['sku' => [], 'field2' => [], 'comment' => [], 'timestamp' => []];
        $schemaName = null;

        $this->schemaListenerMock
            ->expects($this->once())
            ->method('addIndex')
            ->with($tableName, $indexName, $fields, $indexType);

        $adapter
            ->expects($this->once())
            ->method('describeTable')
            ->with($tableName, $schemaName)
            ->willReturn($columns);
        $adapter
            ->expects($this->once())
            ->method('getIndexList')
            ->with($tableName, $schemaName)
            ->willReturn($keyLists);
        $adapter
            ->expects($this->once())
            ->method('_getTableName')
            ->with($tableName, $schemaName)
            ->willReturn($tableName);
        $adapter
            ->method('quoteIdentifier')
            ->willReturnMap([
                [$tableName, false, $this->quoteIdentifier($tableName)],
                [$indexName, false, $this->quoteIdentifier($indexName)],
                [$fields[0], false, $quotedFields[0]],
                [$fields[1], false, $quotedFields[1]],
            ]);
        $adapter
            ->expects($this->once())
            ->method('rawQuery')
            ->with(
                sprintf(
                    $query,
                    $tableName,
                    implode(',', $quotedFields)
                )
            )
            ->willThrowException($exception);
        $adapter
            ->expects($this->exactly((int)in_array(strtolower($indexType), ['primary', 'unique'])))
            ->method('_removeDuplicateEntry')
            ->with($tableName, $fields, $ids)
            ->willThrowException($exception);
        $adapter
            ->expects($this->never())
            ->method('resetDdlCache');

        $adapter->addIndex($tableName, $indexName, $fields, $indexType);
    }

    /**
     * @return array
     */
    public static function addIndexWithDuplicationsInDBDataProvider(): array
    {
        return [
            'New unique index' => [
                'indexName' => 'SOME_UNIQUE_INDEX',
                'indexType' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'keyLists' => [
                    'PRIMARY' => [
                        'INDEX_TYPE' => [
                            AdapterInterface::INDEX_TYPE_PRIMARY
                        ]
                    ],
                ],
                'query' => 'ALTER TABLE `%s` ADD UNIQUE `SOME_UNIQUE_INDEX` (%s)',
                'exceptionMessage' => 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'1-1-1\' '
                    . 'for key \'SOME_UNIQUE_INDEX\', query was: '
                    . 'ALTER TABLE `%s` ADD UNIQUE `SOME_UNIQUE_INDEX` (%s)',
                'ids' => [1, 1, 1],
            ],
            'Existing unique index' => [
                'indexName' => 'SOME_UNIQUE_INDEX',
                'indexType' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'keyLists' => [
                    'PRIMARY' => [
                        'INDEX_TYPE' => [
                            AdapterInterface::INDEX_TYPE_PRIMARY
                        ]
                    ],
                    'SOME_UNIQUE_INDEX' => [
                        'INDEX_TYPE' => [
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ]
                    ],
                ],
                'query' => 'ALTER TABLE `%s` DROP INDEX `SOME_UNIQUE_INDEX`, ADD UNIQUE `SOME_UNIQUE_INDEX` (%s)',
                'exceptionMessage' => 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'1-2-5\' '
                    . 'for key \'SOME_UNIQUE_INDEX\', query was: '
                    . 'ALTER TABLE `%s` DROP INDEX `SOME_UNIQUE_INDEX`, ADD UNIQUE `SOME_UNIQUE_INDEX` (%s)',
                'ids' => [1, 2, 5],
            ],
            'New primary index' => [
                'indexName' => 'PRIMARY',
                'indexType' => AdapterInterface::INDEX_TYPE_PRIMARY,
                'keyLists' => [
                    'SOME_UNIQUE_INDEX' => [
                        'INDEX_TYPE' => [
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ]
                    ],
                ],
                'query' => 'ALTER TABLE `%s` ADD PRIMARY KEY (%s)',
                'exceptionMessage' => 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'1-3-4\' '
                    . 'for key \'PRIMARY\', query was: '
                    . 'ALTER TABLE `%s` ADD PRIMARY KEY (%s)',
                'ids' => [1, 3, 4],
            ],
        ];
    }

    /**
     * @param string $field
     * @return string
     */
    private function quoteIdentifier(string $field): string
    {
        if (strpos($field, '`') !== 0) {
            $field = '`' . $field . '`';
        }

        return $field;
    }

    public function testAddIndexForNonExitingField()
    {
        $tableName = 'core_table';
        $this->expectException(\Zend_Db_Exception::class);
        $this->expectExceptionMessage(sprintf(
            'There is no field "%s" that you are trying to create an index on "%s"',
            'sku',
            $tableName
        ));

        $adapter = $this->getMysqlPdoAdapterMock(['describeTable', 'getIndexList', 'quoteIdentifier', '_getTableName']);

        $fields = ['sku', 'field2'];
        $schemaName = null;

        $adapter
            ->expects($this->once())
            ->method('describeTable')
            ->with($tableName, $schemaName)
            ->willReturn([]);
        $adapter
            ->expects($this->once())
            ->method('getIndexList')
            ->with($tableName, $schemaName)
            ->willReturn([]);
        $adapter
            ->expects($this->once())
            ->method('_getTableName')
            ->with($tableName, $schemaName)
            ->willReturn($tableName);
        $adapter
            ->method('quoteIdentifier')
            ->willReturnMap([
                [$tableName, $tableName],
            ]);

        $adapter->addIndex($tableName, 'SOME_INDEX', $fields);
    }

    /**
     * @return MockObject|PdoMysqlAdapter
     * @throws \ReflectionException
     */
    private function getMysqlPdoAdapterMockForDdlQueryTest(): MockObject
    {
        $mockAdapter = $this->getMysqlPdoAdapterMock(['beginTransaction', 'getTransactionLevel', 'getSchemaListener']);
        $mockAdapter
            ->method('getTransactionLevel')
            ->willReturn(1);

        return $mockAdapter;
    }

    /**
     * @param array $methods
     * @return MockObject|PdoMysqlAdapter
     * @throws \ReflectionException
     */
    private function getMysqlPdoAdapterMock(array $methods): MockObject
    {
        if (empty($methods)) {
            $methods = array_merge($methods, ['query']);
        }
        $methods = array_unique(array_merge($methods, ['getSchemaListener']));

        $string = $this->createMock(StringUtils::class);
        $dateTime = $this->createMock(DateTime::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $selectFactory = $this->getMockBuilder(SelectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapterMock = $this->getMockBuilder(PdoMysqlAdapter::class)
            ->onlyMethods(
                $methods
            )->setConstructorArgs(
                [
                    'string' => $string,
                    'dateTime' => $dateTime,
                    'logger' => $logger,
                    'selectFactory' => $selectFactory,
                    'config' => [
                        'dbname' => 'not_exists',
                        'username' => 'not_valid',
                        'password' => 'not_valid',
                    ],
                    'serializer' => $this->serializerMock,
                ]
            )
            ->getMock();

        $adapterMock
            ->method('getSchemaListener')
            ->willReturn($this->schemaListenerMock);

        /** add profiler Mock */
        $resourceProperty = new \ReflectionProperty(
            get_class($adapterMock),
            '_profiler'
        );
        $resourceProperty->setAccessible(true);
        $resourceProperty->setValue($adapterMock, $this->profiler);

        return $adapterMock;
    }

    /**
     * @param MockObject $pdoAdapterMock
     * @throws \ReflectionException
     */
    private function addConnectionMock(MockObject $pdoAdapterMock): void
    {
        $resourceProperty = new \ReflectionProperty(
            get_class($pdoAdapterMock),
            '_connection'
        );
        $resourceProperty->setAccessible(true);
        $resourceProperty->setValue($pdoAdapterMock, $this->connection);
    }

    /**
     * @param array $actual
     * @param array $expected
     * @dataProvider columnDataForTest
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareColumnData(array $actual, array $expected)
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);
        $result = $this->invokeModelMethod($adapter, 'prepareColumnData', [$actual]);

        foreach ($result as $key => $value) {
            $this->assertEquals($expected[$key], $value);
        }
    }

    /**
     * Data provider for testPrepareColumnData
     *
     * @return array[]
     */
    public static function columnDataForTest(): array
    {
        return [
          [
              'actual' => [
                      [
                          'DATA_TYPE' => 'int',
                          'DEFAULT' => ''
                      ],
                      [
                          'DATA_TYPE' => 'timestamp /* mariadb-5.3 */',
                          'DEFAULT' => 'CURRENT_TIMESTAMP'
                      ],
                      [
                          'DATA_TYPE' => 'varchar',
                          'DEFAULT' => ''
                      ]
                  ],
              'expected' => [
                      [
                          'DATA_TYPE' => 'int',
                          'DEFAULT' => null
                      ],
                      [
                          'DATA_TYPE' => 'timestamp',
                          'DEFAULT' => 'CURRENT_TIMESTAMP'
                      ],
                      [
                          'DATA_TYPE' => 'varchar',
                          'DEFAULT' => ''
                      ]
                  ]
              ]
        ];
    }

    /**
     * @param array $actual
     * @param int|string|\Zend_Db_Expr $expected
     * @dataProvider columnDataAndValueForTest
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareColumnValue(array $actual, int|string|\Zend_Db_Expr $expected)
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);

        $result = $this->invokeModelMethod($adapter, 'prepareColumnValue', [$actual[0], $actual[1]]);

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testPrepareColumnValue
     *
     * @return array[]
     */
    public static function columnDataAndValueForTest(): array
    {
        return [
            [
                'actual' => [
                    [
                        'DATA_TYPE' => 'int',
                        'DEFAULT' => ''
                    ],
                    '10'
                ],
                'expected' => 10
            ],
            [
                'actual' => [
                    [
                        'DATA_TYPE' => 'datetime /* mariadb-5.3 */',
                        'DEFAULT' => 'CURRENT_TIMESTAMP'
                    ],
                    'null'
                ],
                'expected' => new \Zend_Db_Expr('NULL')
            ],
            [
                'actual' => [
                    [
                        'DATA_TYPE' => 'date /* mariadb-5.3 */',
                        'DEFAULT' => ''
                    ],
                    'null'
                ],
                'expected' => new \Zend_Db_Expr('NULL')
            ],
            [
                'actual' => [
                    [
                        'DATA_TYPE' => 'timestamp /* mariadb-5.3 */',
                        'DEFAULT' => 'CURRENT_TIMESTAMP'
                    ],
                    'null'
                ],
                'expected' => new \Zend_Db_Expr('NULL')
            ],
            [
                'actual' => [
                    [
                        'DATA_TYPE' => 'varchar',
                        'NULLABLE' => false,
                        'DEFAULT' => ''
                    ],
                    10
                ],
                'expected' => '10'
            ]
        ];
    }

    /**
     * @param string $actual
     * @param string $expected
     * @dataProvider providerForSanitizeColumnDataType
     * @return void
     * @throws \ReflectionException
     */
    public function testSanitizeColumnDataType(string $actual, string $expected)
    {
        $adapter = $this->getMysqlPdoAdapterMock([]);
        $result = $this->invokeModelMethod($adapter, 'sanitizeColumnDataType', [$actual]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testSanitizeColumnDataType
     *
     * @return array[]
     */
    public static function providerForSanitizeColumnDataType()
    {
        return [
            [
                'actual' => 'int',
                'expected' => 'int'
            ],
            [
                'actual' => 'varchar',
                'expected' => 'varchar'
            ],
            [
                'actual' => 'datetime /* mariadb-5.3 */',
                'expected' => 'datetime'
            ],
            [
                'actual' => 'date /* mariadb-5.3 */',
                'expected' => 'date'
            ],
            [
                'actual' => 'timestamp /* mariadb-5.3 */',
                'expected' => 'timestamp'
            ]
        ];
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokeModelMethod(MockObject $adapter, string $method, array $parameters = [])
    {
        $reflection = new \ReflectionClass($adapter);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($adapter, $parameters);
    }

    /**
     * @dataProvider retryExceptionDataProvider
     * @param \Exception $exception
     * @return void
     */
    public function testBeginTransactionWithReconnect(\Exception $exception): void
    {
        $adapter = $this->getMysqlPdoAdapterMock(['_connect', '_beginTransaction', '_rollBack']);
        $adapter->expects(self::exactly(4))
            ->method('_connect');
        $adapter->expects(self::once())
            ->method('_rollBack');

        $matcher = self::exactly(2);
        $adapter->expects($matcher)
            ->method('_beginTransaction')
            ->willReturnCallback(
                function () use ($exception) {
                    static $counter = 0;
                    if (++$counter === 1) {
                        throw $exception;
                    }
                }
            );
        $adapter->beginTransaction();
        $adapter->rollBack();
    }

    /**
     * @return array[]
     */
    public static function retryExceptionDataProvider(): array
    {
        $serverHasGoneAwayException = new \PDOException();
        $serverHasGoneAwayException->errorInfo = [1 => 2006];
        $lostConnectionException = new \PDOException();
        $lostConnectionException->errorInfo = [1 => 2013];

        return [
            [$serverHasGoneAwayException],
            [$lostConnectionException],
            [new \Zend_Db_Statement_Exception('', 0, $serverHasGoneAwayException)],
            [new \Zend_Db_Statement_Exception('', 0, $lostConnectionException)],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     * @param \Exception $exception
     * @return void
     */
    public function testBeginTransactionWithoutReconnect(\Exception $exception): void
    {
        $this->expectException(\Exception::class);
        $adapter = $this->getMysqlPdoAdapterMock(['_connect', '_beginTransaction', '_rollBack']);
        $adapter->expects(self::once())
            ->method('_connect');
        $adapter->expects(self::once())
            ->method('_beginTransaction')
            ->willThrowException($exception);
        $adapter->beginTransaction();
    }

    /**
     * @return array[]
     */
    public static function exceptionDataProvider(): array
    {
        $pdoException = new \PDOException();
        $pdoException->errorInfo = [1 => 1213];

        return [
            [$pdoException],
            [new \Zend_Db_Statement_Exception('', 0, $pdoException)],
            [new \Exception()],
        ];
    }
}
