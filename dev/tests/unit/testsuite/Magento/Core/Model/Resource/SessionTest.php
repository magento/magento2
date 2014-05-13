<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Resource;

class SessionTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Session\SaveHandler\DbTable
     */
    protected $_model;

    protected function tearDown()
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
        return array(
            'session_encoded' => array('$dataEncoded' => true),
            'session_not_encoded' => array('$dataEncoded' => false)
        );
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
     * @param \PHPUnit_Framework_MockObject_MockObject $connection
     */
    protected function _prepareResourceMock($connection)
    {
        $resource = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getTableName', 'getConnection'),
            array(),
            '',
            false,
            false
        );
        $resource->expects($this->once())->method('getTableName')->will($this->returnValue(self::SESSION_TABLE));
        $resource->expects($this->once())->method('getConnection')->will($this->returnValue($connection));

        $this->_model = new \Magento\Framework\Session\SaveHandler\DbTable($resource);
    }

    /**
     * Prepare mocks for testRead
     *
     * @param bool $isDataEncoded
     */
    protected function _prepareMockForRead($isDataEncoded)
    {
        $connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('select', 'from', 'where', 'fetchOne', 'isTableExists'),
            array(),
            '',
            false
        );
        $connection->expects($this->atLeastOnce())->method('isTableExists')->will($this->returnValue(true));
        $connection->expects($this->once())->method('select')->will($this->returnSelf());
        $connection->expects(
            $this->once()
        )->method(
            'from'
        )->with(
            self::SESSION_TABLE,
            array(self::COLUMN_SESSION_DATA)
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
            array(self::COLUMN_SESSION_ID => self::SESSION_ID)
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
        return array(
            'session_exists' => array('$sessionExists' => true),
            'session_not_exists' => array('$sessionExists' => false)
        );
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
        $connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('select', 'from', 'where', 'fetchOne', 'update', 'insert', 'isTableExists'),
            array(),
            '',
            false
        );
        $connection->expects($this->atLeastOnce())->method('isTableExists')->will($this->returnValue(true));
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
            array(self::COLUMN_SESSION_ID => self::SESSION_ID)
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
                $this->returnCallback(array($this, 'verifyUpdate'))
            );
        } else {
            $connection->expects(
                $this->once()
            )->method(
                'insert'
            )->will(
                $this->returnCallback(array($this, 'verifyInsert'))
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

        $this->assertEquals(array(self::COLUMN_SESSION_ID . '=?' => self::SESSION_ID), $where);
    }
}
