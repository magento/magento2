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
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ACL user resource
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Resource_User extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('admin_user', 'user_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Admin_Model_Resource_User
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(
            array(
                'field' => 'email',
                'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Email')
            ),
            array(
                'field' => 'username',
                'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Name')
            ),
        );
        return $this;
    }

    /**
     * Authenticate user by $username and $password
     *
     * @param Mage_Admin_Model_User $user
     * @return Mage_Admin_Model_Resource_User
     */
    public function recordLogin(Mage_Admin_Model_User $user)
    {
        $adapter = $this->_getWriteAdapter();

        $data = array(
            'logdate' => now(),
            'lognum'  => $user->getLognum() + 1
        );

        $condition = array(
            'user_id = ?' => (int) $user->getUserId(),
        );

        $adapter->update($this->getMainTable(), $data, $condition);

        return $this;
    }

    /**
     * Load data by specified username
     *
     * @param string $username
     * @return false|array
     */
    public function loadByUsername($username)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
                    ->from($this->getMainTable())
                    ->where('username=:username');

        $binds = array(
            'username' => $username
        );

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Check if user is assigned to any role
     *
     * @param int|Mage_Core_Admin_Model_User $user
     * @return null|false|array
     */
    public function hasAssigned2Role($user)
    {
        if (is_numeric($user)) {
            $userId = $user;
        } else if ($user instanceof Mage_Core_Model_Abstract) {
            $userId = $user->getUserId();
        } else {
            return null;
        }

        if ( $userId > 0 ) {
            $adapter = $this->_getReadAdapter();

            $select = $adapter->select();
            $select->from($this->getTable('admin_role'))
                ->where('parent_id > :parent_id')
                ->where('user_id = :user_id');

            $binds = array(
                'parent_id' => 0,
                'user_id' => $userId,
            );

            return $adapter->fetchAll($select, $binds);
        } else {
            return null;
        }
    }

    /**
     * Encrypt password
     *
     * @param string $pwStr
     * @return string
     */
    private function _encryptPassword($pwStr)
    {
        return Mage::helper('Mage_Core_Helper_Data')->getHash($pwStr, 2);
    }

    /**
     * Set created/modified values before user save
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Admin_Model_Resource_User
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $user)
    {
        if ($user->isObjectNew()) {
            $user->setCreated($this->formatDate(true));
        }
        $user->setModified($this->formatDate(true));

        return parent::_beforeSave($user);
    }

    /**
     * Unserialize user extra data after user save
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Admin_Model_Resource_User
     */
    protected function _afterSave(Mage_Core_Model_Abstract $user)
    {
        $user->setExtra(unserialize($user->getExtra()));
        return $this;
    }

    /**
     * Unserialize user extra data after user load
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Admin_Model_Resource_User
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $user)
    {
        if (is_string($user->getExtra())) {
            $user->setExtra(unserialize($user->getExtra()));
        }
        return parent::_afterLoad($user);
    }

    /**
     * Delete user role record with user
     *
     * @param Mage_Core_Model_Abstract $user
     * @return bool
     */
    public function delete(Mage_Core_Model_Abstract $user)
    {
        $this->_beforeDelete($user);
        $adapter = $this->_getWriteAdapter();

        $uid = $user->getId();
        $adapter->beginTransaction();
        try {
            $conditions = array(
                'user_id = ?' => $uid
            );

            $adapter->delete($this->getMainTable(), $conditions);
            $adapter->delete($this->getTable('admin_role'), $conditions);
        } catch (Mage_Core_Exception $e) {
            throw $e;
            return false;
        } catch (Exception $e){
            $adapter->rollBack();
            return false;
        }
        $adapter->commit();
        $this->_afterDelete($user);
        return true;
    }

    /**
     * TODO: unify _saveRelations() and add() methods, they make same things
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Admin_Model_Resource_User
     */
    public function _saveRelations(Mage_Core_Model_Abstract $user)
    {
        $rolesIds = $user->getRoleIds();

        if( !is_array($rolesIds) || count($rolesIds) == 0 ) {
            return $user;
        }

        $adapter = $this->_getWriteAdapter();

        $adapter->beginTransaction();

        try {
            $conditions = array(
                'user_id = ?' => (int) $user->getId(),
            );

            $adapter->delete($this->getTable('admin_role'), $conditions);
            foreach ($rolesIds as $rid) {
                $rid = intval($rid);
                if ($rid > 0) {
                    $row = Mage::getModel('Mage_Admin_Model_Role')->load($rid)->getData();
                } else {
                    $row = array('tree_level' => 0);
                }

                $data = new Varien_Object(array(
                    'parent_id'     => $rid,
                    'tree_level'    => $row['tree_level'] + 1,
                    'sort_order'    => 0,
                    'role_type'     => 'U',
                    'user_id'       => $user->getId(),
                    'role_name'     => $user->getFirstname()
                ));

                $insertData = $this->_prepareDataForTable($data, $this->getTable('admin_role'));
                $adapter->insert($this->getTable('admin_role'), $insertData);
            }
            $adapter->commit();
        } catch (Mage_Core_Exception $e) {
            throw $e;
        } catch (Exception $e){
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Get user roles
     *
     * @param Mage_Core_Model_Abstract $user
     * @return array
     */
    public function getRoles(Mage_Core_Model_Abstract $user)
    {
        if ( !$user->getId() ) {
            return array();
        }

        $table  = $this->getTable('admin_role');
        $adapter   = $this->_getReadAdapter();

        $select = $adapter->select()
                    ->from($table, array())
                    ->joinLeft(
                        array('ar' => $table),
                        "(ar.role_id = {$table}.parent_id and ar.role_type = 'G')",
                        array('role_id'))
                    ->where("{$table}.user_id = :user_id");

        $binds = array(
            'user_id' => (int) $user->getId(),
        );

        $roles = $adapter->fetchCol($select, $binds);

        if ($roles) {
            return $roles;
        }

        return array();
    }

    /**
     * Save user roles
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Admin_Model_Resource_User
     */
    public function add(Mage_Core_Model_Abstract $user)
    {
        $dbh = $this->_getWriteAdapter();

        $aRoles = $this->hasAssigned2Role($user);
        if ( sizeof($aRoles) > 0 ) {
            foreach($aRoles as $idx => $data){
                $conditions = array(
                    'role_id = ?' => $data['role_id'],
                );

                $dbh->delete($this->getTable('admin_role'), $conditions);
            }
        }

        if ($user->getId() > 0) {
            $role = Mage::getModel('Mage_Admin_Model_Role')->load($user->getRoleId());
        } else {
            $role = new Varien_Object();
            $role->setTreeLevel(0);
        }

        $data = new Varien_Object(array(
            'parent_id'  => $user->getRoleId(),
            'tree_level' => ($role->getTreeLevel() + 1),
            'sort_order' => 0,
            'role_type'  => 'U',
            'user_id'    => $user->getUserId(),
            'role_name'  => $user->getFirstname()
        ));

        $insertData = $this->_prepareDataForTable($data, $this->getTable('admin_role'));

        $dbh->insert($this->getTable('admin_role'), $insertData);

        return $this;
    }

    /**
     * Delete user role
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_Admin_Model_Resource_User
     */
    public function deleteFromRole(Mage_Core_Model_Abstract $user)
    {
        if ( $user->getUserId() <= 0 ) {
            return $this;
        }
        if ( $user->getRoleId() <= 0 ) {
            return $this;
        }

        $dbh = $this->_getWriteAdapter();

        $condition = array(
            'user_id = ?'   => (int) $user->getId(),
            'parent_id = ?' => (int) $user->getRoleId(),
        );

        $dbh->delete($this->getTable('admin_role'), $condition);
        return $this;
    }

    /**
     * Check if role user exists
     *
     * @param Mage_Core_Model_Abstract $user
     * @return array|false
     */
    public function roleUserExists(Mage_Core_Model_Abstract $user)
    {
        if ( $user->getUserId() > 0 ) {
            $roleTable = $this->getTable('admin_role');

            $dbh = $this->_getReadAdapter();

            $binds = array(
                'parent_id' => $user->getRoleId(),
                'user_id'   => $user->getUserId(),
            );

            $select = $dbh->select()->from($roleTable)
                ->where('parent_id = :parent_id')
                ->where('user_id = :user_id');

            return $dbh->fetchCol($select, $binds);
        } else {
            return array();
        }
    }

    /**
     * Check if user exists
     *
     * @param Mage_Core_Model_Abstract $user
     * @return array|false
     */
    public function userExists(Mage_Core_Model_Abstract $user)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = array(
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
            'user_id'  => (int) $user->getId(),
        );

        $select->from($this->getMainTable())
            ->where('(username = :username OR email = :email)')
            ->where('user_id <> :user_id');

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Save user extra data
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $data
     * @return Mage_Admin_Model_Resource_User
     */
    public function saveExtra($object, $data)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('extra' => $data),
                array('user_id = ?' => (int) $object->getId())
            );
        }

        return $this;
    }
}
