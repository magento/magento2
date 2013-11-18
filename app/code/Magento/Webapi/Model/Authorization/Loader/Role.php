<?php
/**
 * API ACL Role Loader
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Authorization\Loader;

class Role implements \Magento\Acl\LoaderInterface
{
    /**
     * @var \Magento\Webapi\Model\Resource\Acl\Role
     */
    protected $_roleResource;

    /**
     * @var \Magento\Webapi\Model\Authorization\Role\Factory
     */
    protected $_roleFactory;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\Webapi\Model\Resource\Acl\Role $roleResource
     * @param \Magento\Webapi\Model\Authorization\Role\Factory $roleFactory
     */
    public function __construct(\Magento\Webapi\Model\Resource\Acl\Role $roleResource,
        \Magento\Webapi\Model\Authorization\Role\Factory $roleFactory
    ) {
        $this->_roleResource = $roleResource;
        $this->_roleFactory = $roleFactory;
    }

    /**
     * Populate ACL with roles from external storage.
     *
     * @param \Magento\Acl $acl
     */
    public function populateAcl(\Magento\Acl $acl)
    {
        $roleList = $this->_roleResource->getRolesIds();
        foreach ($roleList as $roleId) {
            /** @var $aclRole \Magento\Webapi\Model\Authorization\Role */
            $aclRole = $this->_roleFactory->createRole(array('roleId' => $roleId));
            $acl->addRole($aclRole);
            //Deny all privileges to Role. Some of them could be allowed later by whitelist
            $acl->deny($aclRole);
        }
    }
}
