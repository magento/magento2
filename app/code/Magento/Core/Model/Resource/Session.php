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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Session save handler
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Resource;

class Session implements \Zend_Session_SaveHandler_Interface
{
    /**
     * Session lifetime
     *
     * @var integer
     */
    protected $_lifeTime;

    /**
     * Session data table name
     *
     * @var string
     */
    protected $_sessionTable;

    /**
     * Database write connection
     *
     * @var \Magento\DB\Adapter\AdapterInterface
     */
    protected $_write;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Constructor
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\App\Dir $dir
     */
    public function __construct(\Magento\Core\Model\Resource $resource, \Magento\App\Dir $dir)
    {
        $this->_sessionTable = $resource->getTableName('core_session');
        $this->_write        = $resource->getConnection('core_write');
        $this->_dir          = $dir;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * Check DB connection
     *
     * @return bool
     */
    public function hasConnection()
    {
        if (!$this->_write) {
            return false;
        }
        if (!$this->_write->isTableExists($this->_sessionTable)) {
            return false;
        }

        return true;
    }

    /**
     * Setup save handler
     *
     * @return \Magento\Core\Model\Resource\Session
     */
    public function setSaveHandler()
    {
        if ($this->hasConnection()) {
            session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'gc')
            );
        } else {
            session_save_path($this->_dir->getDir('session'));
        }
        return $this;
    }

    /**
     * Open session
     *
     * @param string $savePath ignored
     * @param string $sessName ignored
     * @return boolean
     */
    public function open($savePath, $sessName)
    {
        return true;
    }

    /**
     * Close session
     *
     * @return boolean
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
        $select = $this->_write->select()
            ->from($this->_sessionTable, array('session_data'))
            ->where('session_id = :session_id');
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
     * @return boolean
     */
    public function write($sessionId, $sessionData)
    {
        // need to use write connection to get the most fresh DB sessions
        $bindValues = array('session_id' => $sessionId);
        $select = $this->_write->select()
            ->from($this->_sessionTable)
            ->where('session_id = :session_id');
        $exists = $this->_write->fetchOne($select, $bindValues);

        // encode session serialized data to prevent insertion of incorrect symbols
        $sessionData = base64_encode($sessionData);
        $bind = array(
            'session_expires' => time(),
            'session_data'    => $sessionData,
        );

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
     * @param string $sessId
     * @return boolean
     */
    public function destroy($sessId)
    {
        $where = array('session_id = ?' => $sessId);
        $this->_write->delete($this->_sessionTable, $where);
        return true;
    }

    /**
     * Garbage collection
     *
     * @param int $maxLifeTime
     * @return boolean
     */
    public function gc($maxLifeTime)
    {
        $where = array('session_expires < ?' => time() - $maxLifeTime);
        $this->_write->delete($this->_sessionTable, $where);
        return true;
    }
}
