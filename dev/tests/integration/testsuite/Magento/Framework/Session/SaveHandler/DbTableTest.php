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
namespace Magento\Framework\Session\SaveHandler;

class DbTableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test session ID
     */
    const SESSION_ID = 'session_id_value';

    /**#@+
     * Session keys
     */
    const SESSION_NEW = 'session_new';

    const SESSION_EXISTS = 'session_exists';

    /**#@-*/

    /**#@+
     * Table column names
     */
    const COLUMN_SESSION_ID = 'session_id';

    const COLUMN_SESSION_DATA = 'session_data';

    const COLUMN_SESSION_EXPIRES = 'session_expires';

    /**#@-*/

    /**
     * Test session data
     *
     * @var array
     */
    protected $_sourceData = array(
        self::SESSION_NEW => array('new key' => 'new value'),
        self::SESSION_EXISTS => array('existing key' => 'existing value')
    );

    /**
     * Data as objects for serialization
     *
     * @var array
     */
    protected $_sessionData;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Model under test
     *
     * @var \Magento\Framework\Session\SaveHandler\DbTable
     */
    protected $_model;

    /**
     * Write connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Session table name
     *
     * @var string
     */
    protected $_sessionTable;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->get('Magento\Framework\Session\SaveHandler\DbTable');

        /** @var $resource \Magento\Framework\App\Resource */
        $resource = $this->_objectManager->get('Magento\Framework\App\Resource');
        $this->_connection = $resource->getConnection('core_write');
        $this->_sessionTable = $resource->getTableName('core_session');

        // session stores serialized objects with protected properties
        // we need to test this case to ensure that DB adapter successfully processes "\0" symbols in serialized data
        foreach ($this->_sourceData as $key => $data) {
            $this->_sessionData[$key] = new \Magento\Framework\Object($data);
        }
    }

    public function testCheckConnection()
    {
        $method = new \ReflectionMethod('Magento\Framework\Session\SaveHandler\DbTable', 'checkConnection');
        $method->setAccessible(true);
        $this->assertNull($method->invoke($this->_model));
    }

    public function testOpenAndClose()
    {
        $this->assertTrue($this->_model->open('', 'test'));
        $this->assertTrue($this->_model->close());
    }

    public function testWriteReadDestroy()
    {
        $data = serialize($this->_sessionData[self::SESSION_NEW]);
        $this->_model->write(self::SESSION_ID, $data);
        $this->assertEquals($data, $this->_model->read(self::SESSION_ID));

        $data = serialize($this->_sessionData[self::SESSION_EXISTS]);
        $this->_model->write(self::SESSION_ID, $data);
        $this->assertEquals($data, $this->_model->read(self::SESSION_ID));

        $this->_model->destroy(self::SESSION_ID);
        $this->assertEmpty($this->_model->read(self::SESSION_ID));
    }

    public function testGc()
    {
        $this->_model->write('test', 'test');
        $this->assertEquals('test', $this->_model->read('test'));
        $this->_model->gc(-1);
        $this->assertEmpty($this->_model->read('test'));
    }

    /**
     * Assert that session data writes to DB in base64 encoding
     */
    public function testWriteEncoded()
    {
        $data = serialize($this->_sessionData[self::SESSION_NEW]);
        $this->_model->write(self::SESSION_ID, $data);

        $select = $this->_connection->select()->from(
            $this->_sessionTable
        )->where(
            self::COLUMN_SESSION_ID . ' = :' . self::COLUMN_SESSION_ID
        );
        $bind = array(self::COLUMN_SESSION_ID => self::SESSION_ID);
        $session = $this->_connection->fetchRow($select, $bind);

        $this->assertEquals(self::SESSION_ID, $session[self::COLUMN_SESSION_ID]);
        $this->assertTrue(
            ctype_digit((string)$session[self::COLUMN_SESSION_EXPIRES]),
            'Value of session expire field must have integer type'
        );
        $this->assertEquals($data, base64_decode($session[self::COLUMN_SESSION_DATA]));
    }

    /**
     * Data provider for testReadEncoded
     *
     * @return array
     */
    public function readEncodedDataProvider()
    {
        // we can't use object data as a fixture because not encoded serialized object
        // might cause DB adapter fatal error, so we have to use array as a fixture
        $sessionData = serialize($this->_sourceData[self::SESSION_NEW]);
        return array(
            'session_encoded' => array('$sessionData' => base64_encode($sessionData)),
            'session_not_encoded' => array('$sessionData' => $sessionData)
        );
    }

    /**
     * Assert that session data reads from DB correctly regardless of encoding
     *
     * @param string $sessionData
     *
     * @dataProvider readEncodedDataProvider
     */
    public function testReadEncoded($sessionData)
    {
        $sessionRecord = array(self::COLUMN_SESSION_ID => self::SESSION_ID, self::COLUMN_SESSION_DATA => $sessionData);
        $this->_connection->insertOnDuplicate($this->_sessionTable, $sessionRecord, array(self::COLUMN_SESSION_DATA));

        $sessionData = $this->_model->read(self::SESSION_ID);
        $this->assertEquals($this->_sourceData[self::SESSION_NEW], unserialize($sessionData));
    }
}
