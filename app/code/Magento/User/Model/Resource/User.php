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
namespace Magento\User\Model\Resource;

use Magento\User\Model\Acl\Role\Group as RoleGroup;
use Magento\User\Model\Acl\Role\User as RoleUser;
use Magento\User\Model\User as ModelUser;

/**
 * ACL user resource
 */
class User extends \Magento\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Acl\CacheInterface
     */
    protected $_aclCache;

    /**
     * Role model
     *
     * @var \Magento\User\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\App\Resource $resource
     * @param \Magento\Acl\CacheInterface $aclCache
     * @param \Magento\User\Model\RoleFactory $roleFactory
     * @param \Magento\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Acl\CacheInterface $aclCache,
        \Magento\User\Model\RoleFactory $roleFactory,
        \Magento\Stdlib\DateTime $dateTime
    ) {
        parent::__construct($resource);
        $this->_aclCache = $aclCache;
        $this->_roleFactory = $roleFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('admin_user', 'user_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(
            array('field' => 'email', 'title' => __('Email')),
            array('field' => 'username', 'title' => __('User Name'))
        );
        return $this;
    }

    /**
     * Authenticate user by $username and $password
     *
     * @param ModelUser $user
     * @return $this
     */
    public function recordLogin(ModelUser $user)
    {
        $adapter = $this->_getWriteAdapter();

        $data = array('logdate' => $this->dateTime->now(), 'lognum' => $user->getLognum() + 1);

        $condition = array('user_id = ?' => (int)$user->getUserId());

        $adapter->update($this->getMainTable(), $data, $condition);

        return $this;
    }

    /**
     * Load data by specified username
     *
     * @param string $username
     * @return array
     */
    public function loadByUsername($username)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from($this->getMainTable())->where('username=:username');

        $binds = array('username' => $username);

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Check if user is assigned to any role
     *
     * @param int|ModelUser $user
     * @return null|array
     */
    public function hasAssigned2Role($user)
    {
        if (is_numeric($user)) {
            $userId = $user;
        } elseif ($user instanceof \Magento\Model\AbstractModel) {
            $userId = $user->getUserId();
        } else {
            return null;
        }

        if ($userId > 0) {
            $adapter = $this->_getReadAdapter();

            $select = $adapter->select();
            $select->from($this->getTable('admin_role'))->where('parent_id > :parent_id')->where('user_id = :user_id');

            $binds = array('parent_id' => 0, 'user_id' => $userId);

            return $adapter->fetchAll($select, $binds);
        } else {
            return null;
        }
    }

    /**
     * Set created/modified values before user save
     *
     * @param \Magento\Model\AbstractModel $user
     * @return $this
     */
    protected function _beforeSave(\Magento\Model\AbstractModel $user)
    {
        if ($user->isObjectNew()) {
            $user->setCreated($this->dateTime->formatDate(true));
        }
        $user->setModified($this->dateTime->formatDate(true));

        return parent::_beforeSave($user);
    }

    /**
     * Unserialize user extra data after user save
     *
     * @param \Magento\Model\AbstractModel $user
     * @return $this
     */
    protected function _afterSave(\Magento\Model\AbstractModel $user)
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
     * @param ModelUser $user
     * @return void
     */
    public function _clearUserRoles(ModelUser $user)
    {
        $conditions = array('user_id = ?' => (int)$user->getId());
        $this->_getWriteAdapter()->delete($this->getTable('admin_role'), $conditions);
    }

    /**
     * Create role for provided user of provided type
     *
     * @param int $parentId
     * @param ModelUser $user
     * @return void
     */
    protected function _createUserRole($parentId, ModelUser $user)
    {
        if ($parentId > 0) {
            /** @var \Magento\User\Model\Role $parentRole */
            $parentRole = $this->_roleFactory->create()->load($parentId);
        } else {
            $role = new \Magento\Object();
            $role->setTreeLevel(0);
        }

        if ($parentRole->getId()) {
            $data = new \Magento\Object(
                array(
                    'parent_id' => $parentRole->getId(),
                    'tree_level' => $parentRole->getTreeLevel() + 1,
                    'sort_order' => 0,
                    'role_type' => RoleUser::ROLE_TYPE,
                    'user_id' => $user->getId(),
                    'role_name' => $user->getFirstname()
                )
            );

            $insertData = $this->_prepareDataForTable($data, $this->getTable('admin_role'));
            $this->_getWriteAdapter()->insert($this->getTable('admin_role'), $insertData);
            $this->_aclCache->clean();
        }
    }

    /**
     * Unserialize user extra data after user load
     *
     * @param \Magento\Model\AbstractModel $user
     * @return $this
     */
    protected function _afterLoad(\Magento\Model\AbstractModel $user)
    {
        if (is_string($user->getExtra())) {
            $user->setExtra(unserialize($user->getExtra()));
        }
        return parent::_afterLoad($user);
    }

    /**
     * Delete user role record with user
     *
     * @param \Magento\Model\AbstractModel $user
     * @return bool
     * @throws \Magento\Model\Exception
     */
    public function delete(\Magento\Model\AbstractModel $user)
    {
        $this->_beforeDelete($user);
        $adapter = $this->_getWriteAdapter();

        $uid = $user->getId();
        $adapter->beginTransaction();
        try {
            $conditions = array('user_id = ?' => $uid);

            $adapter->delete($this->getMainTable(), $conditions);
            $adapter->delete($this->getTable('admin_role'), $conditions);
        } catch (\Magento\Model\Exception $e) {
            throw $e;
            return false;
        } catch (\Exception $e) {
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
     * @param \Magento\Model\AbstractModel $user
     * @return array
     */
    public function getRoles(\Magento\Model\AbstractModel $user)
    {
        if (!$user->getId()) {
            return array();
        }

        $table = $this->getTable('admin_role');
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $table,
            array()
        )->joinLeft(
            array('ar' => $table),
            "(ar.role_id = {$table}.parent_id and ar.role_type = '" . RoleGroup::ROLE_TYPE . "')",
            array('role_id')
        )->where(
            "{$table}.user_id = :user_id"
        );

        $binds = array('user_id' => (int)$user->getId());

        $roles = $adapter->fetchCol($select, $binds);

        if ($roles) {
            return $roles;
        }

        return array();
    }

    /**
     * Delete user role
     *
     * @param \Magento\Model\AbstractModel $user
     * @return $this
     */
    public function deleteFromRole(\Magento\Model\AbstractModel $user)
    {
        if ($user->getUserId() <= 0) {
            return $this;
        }
        if ($user->getRoleId() <= 0) {
            return $this;
        }

        $dbh = $this->_getWriteAdapter();

        $condition = array('user_id = ?' => (int)$user->getId(), 'parent_id = ?' => (int)$user->getRoleId());

        $dbh->delete($this->getTable('admin_role'), $condition);
        return $this;
    }

    /**
     * Check if role user exists
     *
     * @param \Magento\Model\AbstractModel $user
     * @return array
     */
    public function roleUserExists(\Magento\Model\AbstractModel $user)
    {
        if ($user->getUserId() > 0) {
            $roleTable = $this->getTable('admin_role');

            $dbh = $this->_getReadAdapter();

            $binds = array('parent_id' => $user->getRoleId(), 'user_id' => $user->getUserId());

            $select = $dbh->select()->from($roleTable)->where('parent_id = :parent_id')->where('user_id = :user_id');

            return $dbh->fetchCol($select, $binds);
        } else {
            return array();
        }
    }

    /**
     * Check if user exists
     *
     * @param \Magento\Model\AbstractModel $user
     * @return array
     */
    public function userExists(\Magento\Model\AbstractModel $user)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'user_id' => (int)$user->getId()
        );

        $select->from(
            $this->getMainTable()
        )->where(
            '(username = :username OR email = :email)'
        )->where(
            'user_id <> :user_id'
        );

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Whether a user's identity is confirmed
     *
     * @param \Magento\Model\AbstractModel $user
     * @return bool
     */
    public function isUserUnique(\Magento\Model\AbstractModel $user)
    {
        return !$this->userExists($user);
    }

    /**
     * Save user extra data
     *
     * @param \Magento\Model\AbstractModel $object
     * @param string $data
     * @return $this
     */
    public function saveExtra($object, $data)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('extra' => $data),
                array('user_id = ?' => (int)$object->getId())
            );
        }

        return $this;
    }

    /**
     * Retrieve the total user count bypassing any filters applied to collections
     *
     * @return int
     */
    public function countAll()
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
     * @return \Zend_Validate_Interface $validator
     */
    public function getValidationRulesBeforeSave()
    {
        $userIdentity = new \Zend_Validate_Callback(array($this, 'isUserUnique'));
        $userIdentity->setMessage(
            __('A user with the same user name or email already exists.'),
            \Zend_Validate_Callback::INVALID_VALUE
        );

        return $userIdentity;
    }
}
