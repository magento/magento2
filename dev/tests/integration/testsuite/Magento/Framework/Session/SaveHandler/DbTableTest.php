<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;

class DbTableTest extends \PHPUnit\Framework\TestCase
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
    protected static $_sourceData = [
        self::SESSION_NEW => ['new key' => 'new value'],
        self::SESSION_EXISTS => ['existing key' => 'existing value'],
    ];

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

    /**
     * @var EncryptorInterface
     */
    private $_encryptor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->get(\Magento\Framework\Session\SaveHandler\DbTable::class);

        /** @var $resource \Magento\Framework\App\ResourceConnection */
        $resource = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $this->_connection = $resource->getConnection();
        $this->_sessionTable = $resource->getTableName('session');
        $this->_encryptor = $this->_objectManager->get(EncryptorInterface::class);

        // session stores serialized objects with protected properties
        // we need to test this case to ensure that DB adapter successfully processes "\0" symbols in serialized data
        foreach (self::$_sourceData as $key => $data) {
            $this->_sessionData[$key] = new \Magento\Framework\DataObject($data);
        }
    }

    /**
     * @return void
     */
    public function testCheckConnection()
    {
        $method = new \ReflectionMethod(\Magento\Framework\Session\SaveHandler\DbTable::class, 'checkConnection');
        $method->setAccessible(true);
        $this->assertNull($method->invoke($this->_model));
    }

    /**
     * @return void
     */
    public function testOpenAndClose()
    {
        $this->assertTrue($this->_model->open('', 'test'));
        $this->assertTrue($this->_model->close());
    }

    /**
     * @return void
     */
    public function testWriteReadDestroy()
    {
        // We have to use serialize here.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $data = serialize($this->_sessionData[self::SESSION_NEW]);
        $this->_model->write(self::SESSION_ID, $data);
        $this->assertEquals($data, $this->_model->read(self::SESSION_ID));

        // We have to use serialize here.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $data = serialize($this->_sessionData[self::SESSION_EXISTS]);
        $this->_model->write(self::SESSION_ID, $data);
        $this->assertEquals($data, $this->_model->read(self::SESSION_ID));

        $this->_model->destroy(self::SESSION_ID);
        $this->assertEmpty($this->_model->read(self::SESSION_ID));
    }

    /**
     * @return void
     */
    public function testGc()
    {
        $this->_model->write('test', 'test');
        $this->assertEquals('test', $this->_model->read('test'));
        $this->_model->gc(-1);
        $this->assertEmpty($this->_model->read('test'));
    }

    /**
     * Assert that session data writes to DB in base64 encoding
     *
     * @return void
     */
    public function testWriteEncoded()
    {
        // We have to use serialize here.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $data = serialize($this->_sessionData[self::SESSION_NEW]);
        $this->_model->write(self::SESSION_ID, $data);

        $select = $this->_connection->select()->from(
            $this->_sessionTable
        )->where(
            self::COLUMN_SESSION_ID . ' = :' . self::COLUMN_SESSION_ID
        );
        $bind = [self::COLUMN_SESSION_ID => $this->_encryptor->hash(self::SESSION_ID)];
        $session = $this->_connection->fetchRow($select, $bind);

        $this->assertEquals($this->_encryptor->hash(self::SESSION_ID), $session[self::COLUMN_SESSION_ID]);
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
    public static function readEncodedDataProvider()
    {
        // we can't use object data as a fixture because not encoded serialized object
        // might cause DB adapter fatal error, so we have to use array as a fixture
        // phpcs:ignore Magento2.Security.InsecureFunction
        $sessionData = serialize(self::$_sourceData[self::SESSION_NEW]);
        return [
            'session_encoded' => ['sessionData' => base64_encode($sessionData)],
            'session_not_encoded' => ['sessionData' => $sessionData]
        ];
    }

    /**
     * Assert that session data reads from DB correctly regardless of encoding
     *
     * @param string $sessionData
     *
     * @dataProvider readEncodedDataProvider
     *
     * @return void
     */
    public function testReadEncoded($sessionData)
    {
        $sessionRecord = [self::COLUMN_SESSION_ID => $this->_encryptor->hash(self::SESSION_ID), self::COLUMN_SESSION_DATA => $sessionData];
        $this->_connection->insertOnDuplicate($this->_sessionTable, $sessionRecord, [self::COLUMN_SESSION_DATA]);

        $sessionData = $this->_model->read(self::SESSION_ID);
        // We have to use unserialize here.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->assertEquals(self::$_sourceData[self::SESSION_NEW], unserialize($sessionData));
    }
}
