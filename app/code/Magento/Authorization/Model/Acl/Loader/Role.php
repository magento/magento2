<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Framework\App\ResourceConnection;

class Role implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Authorization\Model\Acl\Role\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Magento\Authorization\Model\Acl\Role\UserFactory
     */
    protected $_roleFactory;

    /**
     * @param \Magento\Authorization\Model\Acl\Role\GroupFactory $groupFactory
     * @param \Magento\Authorization\Model\Acl\Role\UserFactory $roleFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Authorization\Model\Acl\Role\GroupFactory $groupFactory,
        \Magento\Authorization\Model\Acl\Role\UserFactory $roleFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_resource = $resource;
        $this->_groupFactory = $groupFactory;
        $this->_roleFactory = $roleFactory;
    }

    /**
     * Populate ACL with roles from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        $roleTableName = $this->_resource->getTableName('authorization_role');
        $connection = $this->_resource->getConnection();

        $select = $connection->select()->from($roleTableName)->order('tree_level');

        foreach ($connection->fetchAll($select) as $role) {
            $parent = $role['parent_id'] > 0 ? $role['parent_id'] : null;
            switch ($role['role_type']) {
                case RoleGroup::ROLE_TYPE:
                    $acl->addRole($this->_groupFactory->create(['roleId' => $role['role_id']]), $parent);
                    break;

                case RoleUser::ROLE_TYPE:
                    if (!$acl->hasRole($role['role_id'])) {
                        $acl->addRole($this->_roleFactory->create(['roleId' => $role['role_id']]), $parent);
                    } else {
                        $acl->addRoleParent($role['role_id'], $parent);
                    }
                    break;
            }
        }
    }
}
