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
namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;

class Role implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * @var \Magento\Framework\App\Resource
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
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Authorization\Model\Acl\Role\GroupFactory $groupFactory,
        \Magento\Authorization\Model\Acl\Role\UserFactory $roleFactory,
        \Magento\Framework\App\Resource $resource
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
        $adapter = $this->_resource->getConnection('core_read');

        $select = $adapter->select()->from($roleTableName)->order('tree_level');

        foreach ($adapter->fetchAll($select) as $role) {
            $parent = $role['parent_id'] > 0 ? $role['parent_id'] : null;
            switch ($role['role_type']) {
                case RoleGroup::ROLE_TYPE:
                    $acl->addRole($this->_groupFactory->create(array('roleId' => $role['role_id'])), $parent);
                    break;

                case RoleUser::ROLE_TYPE:
                    if (!$acl->hasRole($role['role_id'])) {
                        $acl->addRole($this->_roleFactory->create(array('roleId' => $role['role_id'])), $parent);
                    } else {
                        $acl->addRoleParent($role['role_id'], $parent);
                    }
                    break;
            }
        }
    }
}
