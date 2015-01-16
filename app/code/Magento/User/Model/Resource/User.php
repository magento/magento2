<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\Resource;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\UserContextInterface;
use Magento\User\Model\User as ModelUser;

/**
 * ACL user resource
 */
class User extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Acl\CacheInterface
     */
    protected $_aclCache;

    /**
     * Role model
     *
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Users table
     *
     * @var string
     */
    protected $_usersTable;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Acl\CacheInterface $aclCache
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Acl\CacheInterface $aclCache,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        parent::__construct($resource);
        $this->_aclCache = $aclCache;
        $this->_roleFactory = $roleFactory;
        $this->dateTime = $dateTime;
        $this->_usersTable = $this->getTable('admin_user');
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
        $this->_uniqueFields = [
            ['field' => 'email', 'title' => __('Email')],
            ['field' => 'username', 'title' => __('User Name')],
        ];
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

        $data = ['logdate' => $this->dateTime->now(), 'lognum' => $user->getLognum() + 1];

        $condition = ['user_id = ?' => (int)$user->getUserId()];

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

        $binds = ['username' => $username];

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
        } elseif ($user instanceof \Magento\Framework\Model\AbstractModel) {
            $userId = $user->getUserId();
        } else {
            return null;
        }

        if ($userId > 0) {
            $adapter = $this->_getReadAdapter();

            $select = $adapter->select();
            $select->from($this->getTable('authorization_role'))->where('parent_id > :parent_id')->where('user_id = :user_id');

            $binds = ['parent_id' => 0, 'user_id' => $userId];

            return $adapter->fetchAll($select, $binds);
        } else {
            return null;
        }
    }

    /**
     * Set created/modified values before user save
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $user)
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
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $user)
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
        $conditions = ['user_id = ?' => (int)$user->getId()];
        $this->_getWriteAdapter()->delete($this->getTable('authorization_role'), $conditions);
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
            /** @var \Magento\Authorization\Model\Role $parentRole */
            $parentRole = $this->_roleFactory->create()->load($parentId);
        } else {
            $role = new \Magento\Framework\Object();
            $role->setTreeLevel(0);
        }

        if ($parentRole->getId()) {
            $data = new \Magento\Framework\Object(
                [
                    'parent_id' => $parentRole->getId(),
                    'tree_level' => $parentRole->getTreeLevel() + 1,
                    'sort_order' => 0,
                    'role_type' => RoleUser::ROLE_TYPE,
                    'user_id' => $user->getId(),
                    'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                    'role_name' => $user->getFirstname(),
                ]
            );

            $insertData = $this->_prepareDataForTable($data, $this->getTable('authorization_role'));
            $this->_getWriteAdapter()->insert($this->getTable('authorization_role'), $insertData);
            $this->_aclCache->clean();
        }
    }

    /**
     * Unserialize user extra data after user load
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $user)
    {
        if (is_string($user->getExtra())) {
            $user->setExtra(unserialize($user->getExtra()));
        }
        return parent::_afterLoad($user);
    }

    /**
     * Delete user role record with user
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     */
    public function delete(\Magento\Framework\Model\AbstractModel $user)
    {
        $this->_beforeDelete($user);
        $adapter = $this->_getWriteAdapter();

        $uid = $user->getId();
        $adapter->beginTransaction();
        try {
            $conditions = ['user_id = ?' => $uid];

            $adapter->delete($this->getMainTable(), $conditions);
            $adapter->delete($this->getTable('authorization_role'), $conditions);
        } catch (\Magento\Framework\Model\Exception $e) {
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
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return array
     */
    public function getRoles(\Magento\Framework\Model\AbstractModel $user)
    {
        if (!$user->getId()) {
            return [];
        }

        $table = $this->getTable('authorization_role');
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $table,
            []
        )->joinLeft(
            ['ar' => $table],
            "(ar.role_id = {$table}.parent_id and ar.role_type = '" . RoleGroup::ROLE_TYPE . "')",
            ['role_id']
        )->where(
            "{$table}.user_id = :user_id"
        );

        $binds = ['user_id' => (int)$user->getId()];

        $roles = $adapter->fetchCol($select, $binds);

        if ($roles) {
            return $roles;
        }

        return [];
    }

    /**
     * Delete user role
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    public function deleteFromRole(\Magento\Framework\Model\AbstractModel $user)
    {
        if ($user->getUserId() <= 0) {
            return $this;
        }
        if ($user->getRoleId() <= 0) {
            return $this;
        }

        $dbh = $this->_getWriteAdapter();

        $condition = ['user_id = ?' => (int)$user->getId(), 'parent_id = ?' => (int)$user->getRoleId()];

        $dbh->delete($this->getTable('authorization_role'), $condition);
        return $this;
    }

    /**
     * Check if role user exists
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return array
     */
    public function roleUserExists(\Magento\Framework\Model\AbstractModel $user)
    {
        if ($user->getUserId() > 0) {
            $roleTable = $this->getTable('authorization_role');

            $dbh = $this->_getReadAdapter();

            $binds = ['parent_id' => $user->getRoleId(), 'user_id' => $user->getUserId()];

            $select = $dbh->select()->from($roleTable)->where('parent_id = :parent_id')->where('user_id = :user_id');

            return $dbh->fetchCol($select, $binds);
        } else {
            return [];
        }
    }

    /**
     * Check if user exists
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return array
     */
    public function userExists(\Magento\Framework\Model\AbstractModel $user)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'user_id' => (int)$user->getId(),
        ];

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
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return bool
     */
    public function isUserUnique(\Magento\Framework\Model\AbstractModel $user)
    {
        return !$this->userExists($user);
    }

    /**
     * Save user extra data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $data
     * @return $this
     */
    public function saveExtra($object, $data)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                ['extra' => $data],
                ['user_id = ?' => (int)$object->getId()]
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
        $userIdentity = new \Zend_Validate_Callback([$this, 'isUserUnique']);
        $userIdentity->setMessage(
            __('A user with the same user name or email already exists.'),
            \Zend_Validate_Callback::INVALID_VALUE
        );

        return $userIdentity;
    }

    /**
     * Update role users ACL
     *
     * @param \Magento\Authorization\Model\Role $role
     * @return bool
     */
    public function updateRoleUsersAcl(\Magento\Authorization\Model\Role $role)
    {
        $write = $this->_getWriteAdapter();
        $users = $role->getRoleUsers();
        $rowsCount = 0;

        if (sizeof($users) > 0) {
            $bind = ['reload_acl_flag' => 1];
            $where = ['user_id IN(?)' => $users];
            $rowsCount = $write->update($this->_usersTable, $bind, $where);
        }

        return $rowsCount > 0;
    }
}
