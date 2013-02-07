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
 * @package     Mage_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ACL user resource
 *
 * @category    Mage
 * @package     Mage_User
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_User_Model_Resource_User extends Mage_Core_Model_Resource_Db_Abstract
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
     * @return Mage_User_Model_Resource_User
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(
            array(
                'field' => 'email',
                'title' => Mage::helper('Mage_User_Helper_Data')->__('Email')
            ),
            array(
                'field' => 'username',
                'title' => Mage::helper('Mage_User_Helper_Data')->__('User Name')
            ),
        );
        return $this;
    }

    /**
     * Authenticate user by $username and $password
     *
     * @param Mage_User_Model_User $user
     * @return Mage_User_Model_Resource_User
     */
    public function recordLogin(Mage_User_Model_User $user)
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
     * Set created/modified values before user save
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_User_Model_Resource_User
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
     * @return Mage_User_Model_Resource_User
     */
    protected function _afterSave(Mage_Core_Model_Abstract $user)
    {
        $user->setExtra(unserialize($user->getExtra()));
        if ($user->hasRoleId()) {
            $this->_clearUserRoles($user);
            $this->_createUserRole($user->getRoleId(), $user);
        }
        return $this;
    }

    /**
     * Clear all user-specific roles of provided user
     *
     * @param Mage_User_Model_User $user
     */
    public function _clearUserRoles(Mage_User_Model_User $user)
    {
        $conditions = array(
            'user_id = ?' => (int) $user->getId(),
        );
        $this->_getWriteAdapter()->delete($this->getTable('admin_role'), $conditions);
    }

    /**
     * Create role for provided user of provided type
     *
     * @param $parentId
     * @param Mage_User_Model_User $user
     */
    protected function _createUserRole($parentId, Mage_User_Model_User $user)
    {
        if ($parentId > 0) {
            $parentRole = Mage::getModel('Mage_User_Model_Role')->load($parentId);
        } else {
            $role = new Varien_Object();
            $role->setTreeLevel(0);
        }

        if ($parentRole->getId()) {
            $data = new Varien_Object(array(
                'parent_id'  => $parentRole->getId(),
                'tree_level' => $parentRole->getTreeLevel() + 1,
                'sort_order' => 0,
                'role_type'  => 'U',
                'user_id'    => $user->getId(),
                'role_name'  => $user->getFirstname()
            ));

            $insertData = $this->_prepareDataForTable($data, $this->getTable('admin_role'));
            $this->_getWriteAdapter()->insert($this->getTable('admin_role'), $insertData);
        }
    }

    /**
     * Unserialize user extra data after user load
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_User_Model_Resource_User
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
     * Delete user role
     *
     * @param Mage_Core_Model_Abstract $user
     * @return Mage_User_Model_Resource_User
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
     * Whether a user's identity is confirmed
     *
     * @param Mage_Core_Model_Abstract $user
     * @return bool
     */
    public function isUserUnique(Mage_Core_Model_Abstract $user)
    {
        return !$this->userExists($user);
    }

    /**
     * Save user extra data
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $data
     * @return Mage_User_Model_Resource_User
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

    /**
     * Whether functional restrictions allow to create a new user
     *
     * @return bool
     */
    public function canCreateUser()
    {
        $maxUserCount = (string)Mage::getConfig()->getNode('global/functional_limitation/max_admin_user_count');
        if ('0' === $maxUserCount) {
            return false;
        }
        $maxUserCount = (int)$maxUserCount;
        return ($maxUserCount ? $this->_getTotalUserCount() < $maxUserCount : true);
    }

    /**
     * Whether the functional limitations permit a user saving
     *
     * @param Mage_Core_Model_Abstract $user
     * @return bool
     */
    public function isUserSavingAllowed(Mage_Core_Model_Abstract $user)
    {
        return (!$user->isObjectNew() || $this->canCreateUser());
    }

    /**
     * Retrieve the total user count bypassing any restrictions/filters applied to collections
     *
     * @return int
     */
    protected function _getTotalUserCount()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();
        $select->from($this->getMainTable(), 'COUNT(*)');
        $result = (int)$adapter->fetchOne($select);
        return $result;
    }

    /**
     * Add validation rules to be applied before saving an entity
     *
     * @return Zend_Validate_Interface $validator
     */
    public function getValidationRulesBeforeSave()
    {
        $userIdentity = new Zend_Validate_Callback(array($this, 'isUserUnique'));
        $userIdentity->setMessage(
            Mage::helper('Mage_User_Helper_Data')->__('A user with the same user name or email already exists.'),
            Zend_Validate_Callback::INVALID_VALUE
        );

        $userSavingAllowance = new Zend_Validate_Callback(array($this, 'isUserSavingAllowed'));
        $userSavingAllowance->setMessage(
            $this->getMessageUserCreationProhibited(), Zend_Validate_Callback::INVALID_VALUE
        );

        /** @var $validator Magento_Validator_Composite_VarienObject */
        $validator = new Magento_Validator_Composite_VarienObject;
        $validator
            ->addRule($userIdentity)
            ->addRule($userSavingAllowance)
        ;
        return $validator;
    }

    /**
     * Return the error message to be used when the user creation is prohibited due to the functional restrictions
     *
     * @return string
     */
    public static function getMessageUserCreationProhibited()
    {
        return Mage::helper('Mage_User_Helper_Data')->__('You are using the maximum number of admin accounts allowed.');
    }
}
