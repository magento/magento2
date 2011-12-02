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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
     * Session maximum cookie lifetime
     */
    const SEESION_MAX_COOKIE_LIFETIME = 3155692600;

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
     * Automatic cleaning factor of expired sessions
     * value zero means no automatic cleaning, one means automatic cleaning each time a session is closed, and x>1 means
     * cleaning once in x calls
     *
     * @var int
     */
    protected $_automaticCleaningFactor    = 50;

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
     * Retrieve session life time
     *
     * @return int
     */
    public function getLifeTime()
    {
        if (is_null($this->_lifeTime)) {
            $configNode = Mage::app()->getStore()->isAdmin() ?
                    'admin/security/session_cookie_lifetime' : 'web/cookie/cookie_lifetime';
            $this->_lifeTime = (int) Mage::getStoreConfig($configNode);

            if ($this->_lifeTime < 60) {
                $this->_lifeTime = ini_get('session.gc_maxlifetime');
            }

            if ($this->_lifeTime < 60) {
                $this->_lifeTime = 3600; //one hour
            }

            if ($this->_lifeTime > self::SEESION_MAX_COOKIE_LIFETIME) {
                $this->_lifeTime = self::SEESION_MAX_COOKIE_LIFETIME; // 100 years
            }
        }
        return $this->_lifeTime;
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
        $this->gc($this->getLifeTime());

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
                ->where('session_id = :session_id')
                ->where('session_expires > :session_expires');
        $bind = array(
            'session_id'      => $sessId,
            'session_expires' => Varien_Date::toTimestamp(true)
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
        $bindValues = array(
            'session_id'      => $sessId
        );
        $select = $this->_write->select()
                ->from($this->_sessionTable)
                ->where('session_id = :session_id');
        $exists = $this->_read->fetchOne($select, $bindValues);

        $bind = array(
            'session_expires' => Varien_Date::toTimestamp(true) + $this->getLifeTime(),
            'session_data' => $sessData
        );
        if ($exists) {
            $where = array(
                'session_id=?' => $sessId
            );
            $this->_write->update($this->_sessionTable, $bind, $where);
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
     * @param int $sessMaxLifeTime ignored
     * @return boolean
     */
    public function gc($sessMaxLifeTime)
    {
        if ($this->_automaticCleaningFactor > 0) {
            if ($this->_automaticCleaningFactor == 1 ||
                rand(1, $this->_automaticCleaningFactor) == 1) {
                $where = array('session_expires < ?' => Varien_Date::toTimestamp(true));
                $this->_write->delete($this->_sessionTable, $where);
            }
        }
        return true;
    }
}
