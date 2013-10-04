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
 * @category    Magento
 * @package     Magento_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Model\Acl\Loader;

class Role implements \Magento\Acl\LoaderInterface
{
    /**
     * @var \Magento\Core\Model\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\User\Model\Acl\Role\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Magento\User\Model\Acl\Role\UserFactory
     */
    protected $_roleFactory;

    /**
     * @param \Magento\User\Model\Acl\Role\GroupFactory $groupFactory
     * @param \Magento\User\Model\Acl\Role\UserFactory $roleFactory
     * @param \Magento\Core\Model\Resource $resource
     */
    public function __construct(
        \Magento\User\Model\Acl\Role\GroupFactory $groupFactory,
        \Magento\User\Model\Acl\Role\UserFactory $roleFactory,
        \Magento\Core\Model\Resource $resource
    ) {
        $this->_resource = $resource;
        $this->_groupFactory = $groupFactory;
        $this->_roleFactory = $roleFactory;
    }

    /**
     * Populate ACL with roles from external storage
     *
     * @param \Magento\Acl $acl
     */
    public function populateAcl(\Magento\Acl $acl)
    {
        $roleTableName = $this->_resource->getTableName('admin_role');
        $adapter = $this->_resource->getConnection('core_read');

        $select = $adapter->select()
            ->from($roleTableName)
            ->order('tree_level');

        foreach ($adapter->fetchAll($select) as $role) {
            $parent = ($role['parent_id'] > 0) ?
                \Magento\User\Model\Acl\Role\Group::ROLE_TYPE . $role['parent_id'] : null;
            switch ($role['role_type']) {
                case \Magento\User\Model\Acl\Role\Group::ROLE_TYPE:
                    $roleId = $role['role_type'] . $role['role_id'];
                    $acl->addRole(
                        $this->_groupFactory->create(array('roleId' => $roleId)),
                        $parent
                    );
                    break;

                case \Magento\User\Model\Acl\Role\User::ROLE_TYPE:
                    $roleId = $role['role_type'] . $role['user_id'];
                    if (!$acl->hasRole($roleId)) {
                        $acl->addRole(
                            $this->_roleFactory->create(array('roleId' => $roleId)),
                            $parent
                        );
                    } else {
                        $acl->addRoleParent($roleId, $parent);
                    }
                    break;
            }
        }
    }
}
