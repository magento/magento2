<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class AdminUserRole
 */
class AdminUserRole extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\User\Test\Repository\AdminUserRole';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\User\Test\Handler\AdminUserRole\AdminUserRoleInterface';

    protected $defaultDataSet = [
        'rolename' => 'AdminRole%isolation%',
        'resource_access' => 'All',
    ];

    protected $role_id = [
        'attribute_code' => 'role_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $parent_id = [
        'attribute_code' => 'parent_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $tree_level = [
        'attribute_code' => 'tree_level',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $sort_order = [
        'attribute_code' => 'sort_order',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $role_type = [
        'attribute_code' => 'role_type',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $user_id = [
        'attribute_code' => 'user_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $rolename = [
        'attribute_code' => 'rolename',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'role-info',
    ];

    protected $user_type = [
        'attribute_code' => 'user_type',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $resource_access = [
        'attribute_code' => 'resource_access',
        'backend_type' => 'virtual',
        'group' => 'role-resources',
    ];

    protected $roles_resources = [
        'attribute_code' => 'roles_resources',
        'backend_type' => 'virtual',
        'group' => 'role-resources',
    ];

    protected $in_role_users = [
        'attribute_code' => 'in_role_users',
        'backend_type' => 'virtual',
        'group' => 'in_role_users',
        'source' => 'Magento\User\Test\Fixture\AdminUserRole\InRoleUsers',
    ];

    public function getRoleId()
    {
        return $this->getData('role_id');
    }

    public function getParentId()
    {
        return $this->getData('parent_id');
    }

    public function getTreeLevel()
    {
        return $this->getData('tree_level');
    }

    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    public function getRoleType()
    {
        return $this->getData('role_type');
    }

    public function getUserId()
    {
        return $this->getData('user_id');
    }

    public function getRoleName()
    {
        return $this->getData('rolename');
    }

    public function getUserType()
    {
        return $this->getData('user_type');
    }

    public function getGwsIsAll()
    {
        return $this->getData('gws_is_all');
    }

    public function getGwsWebsites()
    {
        return $this->getData('gws_websites');
    }

    public function getGwsStoreGroups()
    {
        return $this->getData('gws_store_groups');
    }

    public function getResourceAccess()
    {
        return $this->getData('resource_access');
    }

    public function getRolesResources()
    {
        return $this->getData('roles_resources');
    }

    public function getInRoleUsers()
    {
        return $this->getData('in_role_users');
    }
}
