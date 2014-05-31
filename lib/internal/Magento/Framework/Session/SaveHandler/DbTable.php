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
    protected $_write;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->_sessionTable = $resource->getTableName('core_session');
        $this->_write = $resource->getConnection('core_write');
        $this->checkConnection();
    }

    /**
     * Check DB connection
     *
     * @return void
     * @throws \Magento\Framework\Session\SaveHandlerException
     */
    protected function checkConnection()
    {
        if (!$this->_write) {
            throw new \Magento\Framework\Session\SaveHandlerException('Write DB connection is not available');
        }
        if (!$this->_write->isTableExists($this->_sessionTable)) {
            throw new \Magento\Framework\Session\SaveHandlerException('DB storage table does not exist');
        }
    }

    /**
     * Open session
     *
     * @param string $savePath ignored
     * @param string $sessionName ignored
     * @return bool
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
        $select = $this->_write->select()->from(
            $this->_sessionTable,
            array('session_data')
        )->where(
            'session_id = :session_id'
        );
        $bind = array('session_id' => $sessionId);
        $data = $this->_write->fetchOne($select, $bind);

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
        $bindValues = array('session_id' => $sessionId);
        $select = $this->_write->select()->from($this->_sessionTable)->where('session_id = :session_id');
        $exists = $this->_write->fetchOne($select, $bindValues);

        // encode session serialized data to prevent insertion of incorrect symbols
        $sessionData = base64_encode($sessionData);
        $bind = array('session_expires' => time(), 'session_data' => $sessionData);

        if ($exists) {
            $this->_write->update($this->_sessionTable, $bind, array('session_id=?' => $sessionId));
        } else {
            $bind['session_id'] = $sessionId;
            $this->_write->insert($this->_sessionTable, $bind);
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
        $where = array('session_id = ?' => $sessionId);
        $this->_write->delete($this->_sessionTable, $where);
        return true;
    }

    /**
     * Garbage collection
     *
     * @param int $maxLifeTime
     * @return bool
     */
    public function gc($maxLifeTime)
    {
        $where = array('session_expires < ?' => time() - $maxLifeTime);
        $this->_write->delete($this->_sessionTable, $where);
        return true;
    }
}
