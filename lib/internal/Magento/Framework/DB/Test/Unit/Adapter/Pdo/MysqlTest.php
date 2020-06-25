<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Adapter\Pdo;

use Magento\Framework\DB\Adapter\AdapterInterface;
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
    const CUSTOM_ERROR_HANDLER_MESSAGE = 'Custom error handler message';

    /**
     * Adapter for test
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|MockObject
     */
    protected $_adapter;

    /**
     * Mock DB adapter for DDL query tests
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|MockObject
     */
    protected $_mockAdapter;

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
     * Setup
     */
    protected function setUp(): void
    {
        $string = $this->createMock(StringUtils::class);
        $dateTime = $this->createMock(DateTime::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $selectFactory = $this->getMockBuilder(SelectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->schemaListenerMock = $this->getMockBuilder(SchemaListener::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockAdapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->setMethods(['beginTransaction', 'getTransactionLevel', 'getSchemaListener'])
            ->setConstructorArgs(
                [
                    'string' => $string,
                    'dateTime' => $dateTime,
                    'logger' => $logger,
                    'selectFactory' => $selectFactory,
                    'config' => [
                        'dbname' => 'dbname',
                        'username' => 'user',
                        'password' => 'password',
                    ],
                    'serializer' => $this->serializerMock
                ]
            )
            ->getMock();

        $this->_mockAdapter->expects($this->any())
            ->method('getTransactionLevel')
            ->willReturn(1);

        $this->_adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->setMethods(
                [
                    'getCreateTable',
                    '_connect',
                    '_beginTransaction',
                    '_commit',
                    '_rollBack',
                    'query',
                    'fetchRow',
                    'getSchemaListener'
                ]
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
        $this->_mockAdapter->expects($this->any())
            ->method('getSchemaListener')
            ->willReturn($this->schemaListenerMock);
        $this->_adapter->expects($this->any())
            ->method('getSchemaListener')
            ->willReturn($this->schemaListenerMock);

        $profiler = $this->createMock(
            \Zend_Db_Profiler::class
        );

        $resourceProperty = new \ReflectionProperty(
            get_class($this->_adapter),
            '_profiler'
        );
        $resourceProperty->setAccessible(true);
        $resourceProperty->setValue($this->_adapter, $profiler);
    }

    /**
     * @dataProvider bigintResultProvider
     */
    public function testPrepareColumnValueForBigint($value, $expectedResult)
    {
        $result = $this->_adapter->prepareColumnValue(
            ['DATA_TYPE' => 'bigint'],
            $value
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data Provider for testPrepareColumnValueForBigint
     */
    public function bigintResultProvider()
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
        try {
            $this->_mockAdapter->query($query);
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                $e->getMessage(),
                AdapterInterface::ERROR_DDL_MESSAGE
            );
        }

        $select = new Select($this->_mockAdapter, new SelectRenderer([]));
        $select->from('user');
        try {
            $this->_mockAdapter->query($select);
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
        $this->_mockAdapter->query($ddlQuery);
    }

    public function testMultipleQueryException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Multiple queries can\'t be executed. Run a single query and try again.');
        $sql = "SELECT COUNT(*) AS _num FROM test; ";
        $sql .= "INSERT INTO test(id) VALUES (1); ";
        $sql .= "SELECT COUNT(*) AS _num FROM test; ";
        $this->_mockAdapter->query($sql);
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
        $this->expectExceptionMessage(AdapterInterface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE);
        $this->_adapter->rollBack();
    }

    /**
     * Test Asymmetric transaction commit failure
     */
    public function testAsymmetricCommitFailure()
    {
        $this->expectExceptionMessage(AdapterInterface::ERROR_ASYMMETRIC_COMMIT_MESSAGE);
        $this->_adapter->commit();
    }

    /**
     * Test Asymmetric transaction commit success
     */
    public function testAsymmetricCommitSuccess()
    {
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
        $this->_adapter->beginTransaction();
        $this->assertEquals(1, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test Asymmetric transaction rollback success
     */
    public function testAsymmetricRollBackSuccess()
    {
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
        $this->_adapter->beginTransaction();
        $this->assertEquals(1, $this->_adapter->getTransactionLevel());
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successful nested transaction
     */
    public function testNestedTransactionCommitSuccess()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_commit');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successful nested transaction
     */
    public function testNestedTransactionRollBackSuccess()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successful nested transaction
     */
    public function testNestedTransactionLastRollBack()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     * phpcs:disable Magento2.Exceptions.ThrowCatch
     */
    public function testIncompleteRollBackFailureOnCommit()
    {
        $this->_adapter->expects($this->exactly(2))->method('_connect');

        try {
            $this->_adapter->beginTransaction();
            $this->_adapter->beginTransaction();
            $this->_adapter->rollBack();
            $this->_adapter->commit();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $this->_adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     * phpcs:disable Magento2.Exceptions.ThrowCatch
     */
    public function testIncompleteRollBackFailureOnBeginTransaction()
    {
        $this->_adapter->expects($this->exactly(2))->method('_connect');

        try {
            $this->_adapter->beginTransaction();
            $this->_adapter->beginTransaction();
            $this->_adapter->rollBack();
            $this->_adapter->beginTransaction();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $this->_adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testSequentialTransactionsSuccess()
    {
        $this->_adapter->expects($this->exactly(4))
            ->method('_connect');
        $this->_adapter->expects($this->exactly(2))
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');
        $this->_adapter->expects($this->once())
            ->method('_commit');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();

        $this->_adapter->beginTransaction();
        $this->_adapter->commit();
    }

    /**
     * Test that column names are quoted in ON DUPLICATE KEY UPDATE section
     */
    public function testInsertOnDuplicateWithQuotedColumnName()
    {
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
        $this->_adapter->expects($this->once())
            ->method('query')
            ->with($sqlQuery, $bind)
            ->willReturn($stmtMock);

        $this->_adapter->insertOnDuplicate($table, $data, $fields);
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
        $connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['tableColumnExists', '_getTableName', 'rawQuery', 'resetDdlCache', 'quote', 'getSchemaListener']
        );
        $connectionMock->expects($this->any())->method('getSchemaListener')->willReturn($this->schemaListenerMock);
        $connectionMock->expects($this->any())->method('_getTableName')->willReturnArgument(0);
        $connectionMock->expects($this->any())->method('quote')->willReturnArgument(0);
        $connectionMock->expects($this->once())->method('rawQuery')->with($expectedQuery);
        $connectionMock->addColumn('tableName', 'columnName', $options);
    }

    /**
     * @return array
     */
    public function addColumnDataProvider()
    {
        return [
            [
                'columnData' => [
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
        $resultIndexName = $this->_mockAdapter->getIndexName($name, $fields, $indexType);
        $this->assertStringStartsWith($expectedName, $resultIndexName);
    }

    /**
     * @return array
     */
    public function getIndexNameDataProvider()
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
}
