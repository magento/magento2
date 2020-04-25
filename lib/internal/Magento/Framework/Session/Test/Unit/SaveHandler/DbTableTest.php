<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Test\Unit\SaveHandler;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Session\SaveHandler\DbTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DbTableTest extends TestCase
{
    /**
     * Session table name
     */
    const SESSION_TABLE = 'session_table_name';

    /**#@+
     * Table column names
     */
    const COLUMN_SESSION_ID = 'session_id';

    const COLUMN_SESSION_DATA = 'session_data';

    const COLUMN_SESSION_EXPIRES = 'session_expires';

    /**#@-*/

    /**
     * Test select object
     */
    const SELECT_OBJECT = 'select_object';

    /**#@+
     * Test session data
     */
    const SESSION_ID = 'custom_session_id';

    const SESSION_DATA = 'custom_session_data';

    /**#@-*/
    /**
     * Model under test
     *
     * @var DbTable
     */
    protected $_model;

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    /**
     * Data provider for testRead
     *
     * @return array
     */
    public function readDataProvider()
    {
        return [
            'session_encoded' => ['$dataEncoded' => true],
            'session_not_encoded' => ['$dataEncoded' => false]
        ];
    }

    public function testCheckConnection()
    {
        $connection = $this->createPartialMock(Mysql::class, ['isTableExists']);
        $connection->expects(
            $this->atLeastOnce()
        )->method(
            'isTableExists'
        )->with(
            $this->equalTo(self::SESSION_TABLE)
        )->will(
            $this->returnValue(true)
        );

        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->once())->method('getTableName')->will($this->returnValue(self::SESSION_TABLE));
        $resource->expects($this->once())->method('getConnection')->will($this->returnValue($connection));

        $this->_model = new DbTable($resource);

        $method = new \ReflectionMethod(DbTable::class, 'checkConnection');
        $method->setAccessible(true);
        $this->assertNull($method->invoke($this->_model));
    }

    public function testCheckConnectionNoConnection()
    {
        $this->expectException('Magento\Framework\Exception\SessionException');
        $this->expectExceptionMessage('The write connection to the database isn\'t available. Please try again later.');
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->once())->method('getTableName')->will($this->returnValue(self::SESSION_TABLE));
        $resource->expects($this->once())->method('getConnection')->will($this->returnValue(null));

        $this->_model = new DbTable($resource);

        $method = new \ReflectionMethod(DbTable::class, 'checkConnection');
        $method->setAccessible(true);
        $this->assertNull($method->invoke($this->_model));
    }

    public function testCheckConnectionNoTable()
    {
        $this->expectException('Magento\Framework\Exception\SessionException');
        $this->expectExceptionMessage('The database storage table doesn\'t exist. Verify the table and try again.');
        $connection = $this->createPartialMock(Mysql::class, ['isTableExists']);
        $connection->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $this->equalTo(self::SESSION_TABLE)
        )->will(
            $this->returnValue(false)
        );

        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->once())->method('getTableName')->will($this->returnValue(self::SESSION_TABLE));
        $resource->expects($this->once())->method('getConnection')->will($this->returnValue($connection));

        $this->_model = new DbTable($resource);

        $method = new \ReflectionMethod(DbTable::class, 'checkConnection');
        $method->setAccessible(true);
        $this->assertNull($method->invoke($this->_model));
    }

    /**
     * @param bool $isDataEncoded
     *
     * @dataProvider readDataProvider
     */
    public function testRead($isDataEncoded)
    {
        $this->_prepareMockForRead($isDataEncoded);
        $result = $this->_model->read(self::SESSION_ID);
        $this->assertEquals(self::SESSION_DATA, $result);
    }

    /**
     * Prepares mock for test model with specified connections
     *
     * @param MockObject $connection
     */
    protected function _prepareResourceMock($connection)
    {
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->once())->method('getTableName')->will($this->returnValue(self::SESSION_TABLE));
        $resource->expects($this->once())->method('getConnection')->will($this->returnValue($connection));

        $this->_model = new DbTable($resource);
    }

    /**
     * Prepare mocks for testRead
     *
     * @param bool $isDataEncoded
     */
    protected function _prepareMockForRead($isDataEncoded)
    {
        $connection = $this->createPartialMock(
            Mysql::class,
            ['select', 'from', 'where', 'fetchOne', 'isTableExists']
        );

        $connection->expects($this->once())->method('isTableExists')->will($this->returnValue(true));

        $connection->expects($this->once())->method('select')->will($this->returnSelf());
        $connection->expects(
            $this->once()
        )->method(
            'from'
        )->with(
            self::SESSION_TABLE,
            [self::COLUMN_SESSION_DATA]
        )->will(
            $this->returnSelf()
        );
        $connection->expects(
            $this->once()
        )->method(
            'where'
        )->with(
            self::COLUMN_SESSION_ID . ' = :' . self::COLUMN_SESSION_ID
        )->will(
            $this->returnValue(self::SELECT_OBJECT)
        );

        $sessionData = self::SESSION_DATA;
        if ($isDataEncoded) {
            $sessionData = base64_encode($sessionData);
        }
        $connection->expects(
            $this->once()
        )->method(
            'fetchOne'
        )->with(
            self::SELECT_OBJECT,
            [self::COLUMN_SESSION_ID => self::SESSION_ID]
        )->will(
            $this->returnValue($sessionData)
        );

        $this->_prepareResourceMock($connection);
    }

    /**
     * Data provider for testWrite
     *
     * @return array
     */
    public function writeDataProvider()
    {
        return [
            'session_exists' => ['$sessionExists' => true],
            'session_not_exists' => ['$sessionExists' => false]
        ];
    }

    /**
     * @param bool $sessionExists
     *
     * @dataProvider writeDataProvider
     */
    public function testWrite($sessionExists)
    {
        $this->_prepareMockForWrite($sessionExists);
        $this->assertTrue($this->_model->write(self::SESSION_ID, self::SESSION_DATA));
    }

    /**
     * Prepare mocks for testWrite
     *
     * @param bool $sessionExists
     */
    protected function _prepareMockForWrite($sessionExists)
    {
        $connection = $this->createPartialMock(
            Mysql::class,
            ['select', 'from', 'where', 'fetchOne', 'update', 'insert', 'isTableExists']
        );
        $connection->expects($this->once())->method('isTableExists')->will($this->returnValue(true));
        $connection->expects($this->once())->method('select')->will($this->returnSelf());
        $connection->expects($this->once())->method('from')->with(self::SESSION_TABLE)->will($this->returnSelf());
        $connection->expects(
            $this->once()
        )->method(
            'where'
        )->with(
            self::COLUMN_SESSION_ID . ' = :' . self::COLUMN_SESSION_ID
        )->will(
            $this->returnValue(self::SELECT_OBJECT)
        );
        $connection->expects(
            $this->once()
        )->method(
            'fetchOne'
        )->with(
            self::SELECT_OBJECT,
            [self::COLUMN_SESSION_ID => self::SESSION_ID]
        )->will(
            $this->returnValue($sessionExists)
        );

        if ($sessionExists) {
            $connection->expects($this->never())->method('insert');
            $connection->expects(
                $this->once()
            )->method(
                'update'
            )->will(
                $this->returnCallback([$this, 'verifyUpdate'])
            );
        } else {
            $connection->expects(
                $this->once()
            )->method(
                'insert'
            )->will(
                $this->returnCallback([$this, 'verifyInsert'])
            );
            $connection->expects($this->never())->method('update');
        }

        $this->_prepareResourceMock($connection);
    }

    /**
     * Verify arguments of insert method
     *
     * @param string $table
     * @param array $bind
     */
    public function verifyInsert($table, array $bind)
    {
        $this->assertEquals(self::SESSION_TABLE, $table);

        $this->assertInternalType('int', $bind[self::COLUMN_SESSION_EXPIRES]);
        $this->assertEquals(base64_encode(self::SESSION_DATA), $bind[self::COLUMN_SESSION_DATA]);
        $this->assertEquals(self::SESSION_ID, $bind[self::COLUMN_SESSION_ID]);
    }

    /**
     * Verify arguments of update method
     *
     * @param string $table
     * @param array $bind
     * @param array $where
     */
    public function verifyUpdate($table, array $bind, array $where)
    {
        $this->assertEquals(self::SESSION_TABLE, $table);

        $this->assertInternalType('int', $bind[self::COLUMN_SESSION_EXPIRES]);
        $this->assertEquals(base64_encode(self::SESSION_DATA), $bind[self::COLUMN_SESSION_DATA]);

        $this->assertEquals([self::COLUMN_SESSION_ID . '=?' => self::SESSION_ID], $where);
    }
}
