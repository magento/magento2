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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ACL user resource
 *
 * @category    Mage
 * @package     Mage_Api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Resource_User extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('api_user', 'user_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Api_Model_Resource_User
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(
            array(
                'field' => 'email',
                'title' => Mage::helper('Mage_Api_Helper_Data')->__('Email')
            ),
            array(
                'field' => 'username',
                'title' => Mage::helper('Mage_Api_Helper_Data')->__('User Name')
            ),
        );
        return $this;
    }

    /**
     * Authenticate user by $username and $password
     *
     * @param Mage_Api_Model_User $user
     * @return Mage_Api_Model_Resource_User
     */
    public function recordLogin(Mage_Api_Model_User $user)
    {
        $data = array(
            'lognum'  => $user->getLognum()+1,
        );
        $condition = $this->_getReadAdapter()->quoteInto('user_id=?', $user->getUserId());
        $this->_getWriteAdapter()->update($this->getTable('api_user'), $data, $condition);
        return $this;
    }

    /**
     * Record api user session
     *
     * @param Mage_Api_Model_User $user
     * @return Mage_Api_Model_Resource_User
     */
    public function recordSession(Mage_Api_Model_User $user)
    {
        $readAdapter    = $this->_getReadAdapter();
        $writeAdapter   = $this->_getWriteAdapter();
        $select = $readAdapter->select()
            ->from($this->getTable('api_session'), 'user_id')
            ->where('user_id = ?', $user->getId())
            ->where('sessid = ?', $user->getSessid());
        $loginDate = now();
        if ($readAdapter->fetchRow($select)) {
            $writeAdapter->update(
                $this->getTable('api_session'),
                array ('logdate' => $loginDate),
                $readAdapter->quoteInto('user_id = ?', $user->getId()) . ' AND '
                . $readAdapter->quoteInto('sessid = ?', $user->getSessid())
            );
        } else {
            $writeAdapter->insert(
                $this->getTable('api_session'),
                array(
                    'user_id' => $user->getId(),
                    'logdate' => $loginDate,
                    'sessid' => $user->getSessid()
                )
            );
        }
        $user->setLogdate($loginDate);
        return $this;
    }

    /**
     * Clean old session
     *
     * @param Mage_Api_Model_User $user
     * @return Mage_Api_Model_Resource_User
     */
    public function cleanOldSessions(Mage_Api_Model_User $user)
    {
        $readAdapter    = $this->_getReadAdapter();
        $writeAdapter   = $this->_getWriteAdapter();
        $timeout        = Mage::getStoreConfig('api/config/session_timeout');
        $timeSubtract     = $readAdapter->getDateAddSql(
            'logdate',
            $timeout,
            Varien_Db_Adapter_Interface::INTERVAL_SECOND);
        $writeAdapter->delete(
            $this->getTable('api_session'),
            array('user_id = ?' => $user->getId(), $readAdapter->quote(now()) . ' > '.$timeSubtract)
        );
        return $this;
    }

    /**
     * Load data by username
     *
     * @param string $username
     * @return array
     */
    public function loadByUsername($username)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from($this->getTable('api_user'))
            ->where('username=:username');
        return $adapter->fetchRow($select, array('username'=>$username));
    }

    /**
     * load by session id
     *
     * @param string $sessId
     * @return array
     */
    public function loadBySessId($sessId)
    {
        $result = array();
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('api_session'))
            ->where('sessid = ?', $sessId);
        if ($apiSession = $adapter->fetchRow($select)) {
            $selectUser = $adapter->select()
                ->from($this->getTable('api_user'))
                ->where('user_id = ?', $apiSession['user_id']);
                if ($user = $adapter->fetchRow($selectUser)) {
                    $result = array_merge($user, $apiSession);
                }
        }
        return $result;
    }

    /**
     * Clear by session
     *
     * @param string $sessid
     * @return Mage_Api_Model_Resource_User
     */
    public function clearBySessId($sessid)
    {
        $this->_getWriteAdapter()->delete(
            $this->getTable('api_session'),
            array('sessid = ?' => $sessid)
        );
        return $this;
    }

    /**
     * Retrieve api user role data if it was assigned to role
     *
     * @param int | Mage_Api_Model_User $user
     * @return null | array
     */
    public function hasAssigned2Role($user)
    {
        $userId = null;
        $result = null;
        if (is_numeric($user)) {
            $userId = $user;
        } else if ($user instanceof Mage_Core_Model_Abstract) {
            $userId = $user->getUserId();
        }

        if ($userId) {
            $adapter = $this->_getReadAdapter();
            $select = $adapter->select();
            $select->from($this->getTable('api_role'))
                ->where('parent_id > 0 AND user_id = ?', $userId);
            $result = $adapter->fetchAll($select);
        }
        return $result;
    }

    /**
     * Action before save
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Api_Model_Resource_User
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $user)
    {
        if (!$user->getId()) {
            $user->setCreated(now());
        }
        $user->setModified(now());
        return $this;
    }

    /**
     * Delete the object
     *
     * @param Mage_Core_Model_Abstract $user
     * @return boolean
     */
    public function delete(Mage_Core_Model_Abstract $user)
    {
        $dbh = $this->_getWriteAdapter();
        $uid = (int) $user->getId();
        $dbh->beginTransaction();
        try {
            $dbh->delete($this->getTable('api_user'), array('user_id = ?' => $uid));
            $dbh->delete($this->getTable('api_role'), array('user_id = ?' => $uid));
        } catch (Mage_Core_Exception $e) {
            throw $e;
            return false;
        } catch (Exception $e) {
            $dbh->rollBack();
            return false;
        }
        $dbh->commit();
        return true;
    }

    /**
     * Save user roles
     *
     * @param Mage_Core_Model_Abstract $user
     * @return unknown
     */
    public function _saveRelations(Mage_Core_Model_Abstract $user)
    {
        $rolesIds = $user->getRoleIds();
        if (!is_array($rolesIds) || count($rolesIds) == 0) {
            return $user;
        }

        $adapter = $this->_getWriteAdapter();

        $adapter->beginTransaction();

        try {
            $adapter->delete(
                $this->getTable('api_role'),
                array('user_id = ?' => (int) $user->getId()));
            foreach ($rolesIds as $rid) {
                $rid = intval($rid);
                if ($rid > 0) {
                    //$row = $this->load($user, $rid);
                } else {
                    $row = array('tree_level' => 0);
                }
                $row = array('tree_level' => 0);

                $data = array(
                    'parent_id'     => $rid,
                    'tree_level'    => $row['tree_level'] + 1,
                    'sort_order'    => 0,
                    'role_type'     => Mage_Api_Model_Acl::ROLE_TYPE_USER,
                    'user_id'       => $user->getId(),
                    'role_name'     => $user->getFirstname()
                );
                $adapter->insert($this->getTable('api_role'), $data);
            }
            $adapter->commit();
        } catch (Mage_Core_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $adapter->rollBack();
        }
        return $this;
    }

    /**
     * Retrieve roles data
     *
     * @param Mage_Core_Model_Abstract $user
     * @return array
     */
    public function _getRoles(Mage_Core_Model_Abstract $user)
    {
        if (!$user->getId()) {
            return array();
        }
        $table   = $this->getTable('api_role');
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($table, array())
            ->joinLeft(
                array('ar' => $table),
                $adapter->quoteInto(
                    "ar.role_id = {$table}.parent_id AND ar.role_type = ?",
                    Mage_Api_Model_Acl::ROLE_TYPE_GROUP),
                array('role_id'))
            ->where("{$table}.user_id = ?", $user->getId());

        return (($roles = $adapter->fetchCol($select)) ? $roles : array());
    }

    /**
     * Add Role
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Api_Model_Resource_User
     */
    public function add(Mage_Core_Model_Abstract $user)
    {
        $adapter = $this->_getWriteAdapter();
        $aRoles  = $this->hasAssigned2Role($user);
        if (sizeof($aRoles) > 0) {
            foreach ($aRoles as $idx => $data) {
                $adapter->delete(
                    $this->getTable('api_role'),
                    array('role_id = ?' => $data['role_id'])
                );
            }
        }

        if ($user->getId() > 0) {
            $role = Mage::getModel('Mage_Api_Model_Role')->load($user->getRoleId());
        } else {
            $role = new Varien_Object(array('tree_level' => 0));
        }
        $adapter->insert($this->getTable('api_role'), array(
            'parent_id' => $user->getRoleId(),
            'tree_level'=> ($role->getTreeLevel() + 1),
            'sort_order'=> 0,
            'role_type' => Mage_Api_Model_Acl::ROLE_TYPE_USER,
            'user_id'   => $user->getUserId(),
            'role_name' => $user->getFirstname()
        ));

        return $this;
    }

    /**
     * Delete from role
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Api_Model_Resource_User
     */
    public function deleteFromRole(Mage_Core_Model_Abstract $user)
    {
        if ($user->getUserId() <= 0) {
            return $this;
        }
        if ($user->getRoleId() <= 0) {
            return $this;
        };

        $adapter   = $this->_getWriteAdapter();
        $table     = $this->getTable('api_role');

        $condition = array(
            "{$table}.user_id = ?"  => $user->getUserId(),
            "{$table}.parent_id = ?"=> $user->getRoleId()
        );
        $adapter->delete($table, $condition);
        return $this;
    }

    /**
     * Retrieve roles which exists for user
     *
     * @param Mage_Core_Model_Abstract $user
     * @return array
     */
    public function roleUserExists(Mage_Core_Model_Abstract $user)
    {
        $result = array();
        if ($user->getUserId() > 0) {
            $adapter    = $this->_getReadAdapter();
            $select     = $adapter->select()->from($this->getTable('api_role'))
                ->where('parent_id = ?', $user->getRoleId())
                ->where('user_id = ?', $user->getUserId());
            $result = $adapter->fetchCol($select);
        }
        return $result;
    }

    /**
     * Check if user not unique
     *
     * @param Mage_Core_Model_Abstract $user
     * @return array
     */
    public function userExists(Mage_Core_Model_Abstract $user)
    {
        $usersTable = $this->getTable('api_user');
        $adapter    = $this->_getReadAdapter();
        $condition  = array(
            $adapter->quoteInto("{$usersTable}.username = ?", $user->getUsername()),
            $adapter->quoteInto("{$usersTable}.email = ?", $user->getEmail()),
        );
        $select = $adapter->select()
            ->from($usersTable)
            ->where(implode(' OR ', $condition))
            ->where($usersTable.'.user_id != ?', (int) $user->getId());
        return $adapter->fetchRow($select);
    }
}
