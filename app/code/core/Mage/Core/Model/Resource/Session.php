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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Session save handler
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Session implements Zend_Session_SaveHandler_Interface
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
     * Database read connection
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_read;

    /**
     * Database write connection
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_write;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('Mage_Core_Model_Resource');
        $this->_sessionTable = $resource->getTableName('core_session');
        $this->_read         = $resource->getConnection('core_read');
        $this->_write        = $resource->getConnection('core_write');
    }

    /**
     * Destrucor
     *
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
        if (!$this->_read) {
            return false;
        }
        if (!$this->_read->isTableExists($this->_sessionTable)) {
            return false;
        }

        return true;
    }

    /**
     * Setup save handler
     *
     * @return Mage_Core_Model_Resource_Session
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
            session_save_path(Mage::getBaseDir('session'));
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
     * @param string $sessId
     * @return string
     */
    public function read($sessId)
    {
        $select = $this->_read->select()
                ->from($this->_sessionTable, array('session_data'))
                ->where('session_id = :session_id');
        $bind = array(
            'session_id'      => $sessId,
        );

        $data = $this->_read->fetchOne($select, $bind);
        return $data;
    }

    /**
     * Update session
     *
     * @param string $sessId
     * @param string $sessData
     * @return boolean
     */
    public function write($sessId, $sessData)
    {
        $bindValues = array('session_id' => $sessId);
        $select = $this->_write->select()
                ->from($this->_sessionTable)
                ->where('session_id = :session_id');
        $exists = $this->_read->fetchOne($select, $bindValues);

        $bind = array(
            'session_expires' => time(),
            'session_data' => $sessData
        );

        if ($exists) {
            $this->_write->update($this->_sessionTable, $bind, array('session_id=?' => $sessId));
        } else {
            $bind['session_id'] = $sessId;
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
