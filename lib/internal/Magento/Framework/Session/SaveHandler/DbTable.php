<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Phrase;

/**
 * Data base session save handler
 */
class DbTable extends \SessionHandler
{
    /**
     * Session data table name
     *
     * @var string
     */
    protected $_sessionTable;

    /**
     * Database write connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->_sessionTable = $resource->getTableName('session');
        $this->connection = $resource->getConnection();
        $this->checkConnection();
    }

    /**
     * Check DB connection
     *
     * @return void
     * @throws \Magento\Framework\Exception\SessionException
     */
    protected function checkConnection()
    {
        if (!$this->connection) {
            throw new SessionException(new Phrase('Write DB connection is not available'));
        }
        if (!$this->connection->isTableExists($this->_sessionTable)) {
            throw new SessionException(new Phrase('DB storage table does not exist'));
        }
    }

    /**
     * Open session
     *
     * @param string $savePath ignored
     * @param string $sessionName ignored
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Close session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Fetch session data
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        // need to use write connection to get the most fresh DB sessions
        $select = $this->connection->select()->from(
            $this->_sessionTable,
            ['session_data']
        )->where(
            'session_id = :session_id'
        );
        $bind = ['session_id' => $sessionId];
        $data = $this->connection->fetchOne($select, $bind);

        // check if session data is a base64 encoded string
        $decodedData = base64_decode($data, true);
        if ($decodedData !== false) {
            $data = $decodedData;
        }
        return $data;
    }

    /**
     * Update session
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        // need to use write connection to get the most fresh DB sessions
        $bindValues = ['session_id' => $sessionId];
        $select = $this->connection->select()->from($this->_sessionTable)->where('session_id = :session_id');
        $exists = $this->connection->fetchOne($select, $bindValues);

        // encode session serialized data to prevent insertion of incorrect symbols
        $sessionData = base64_encode($sessionData);
        $bind = ['session_expires' => time(), 'session_data' => $sessionData];

        if ($exists) {
            $this->connection->update($this->_sessionTable, $bind, ['session_id=?' => $sessionId]);
        } else {
            $bind['session_id'] = $sessionId;
            $this->connection->insert($this->_sessionTable, $bind);
        }
        return true;
    }

    /**
     * Destroy session
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $where = ['session_id = ?' => $sessionId];
        $this->connection->delete($this->_sessionTable, $where);
        return true;
    }

    /**
     * Garbage collection
     *
     * @param int $maxLifeTime
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function gc($maxLifeTime)
    {
        $where = ['session_expires < ?' => time() - $maxLifeTime];
        $this->connection->delete($this->_sessionTable, $where);
        return true;
    }
}
