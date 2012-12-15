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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Authorization_Loader_Role implements Magento_Acl_Loader
{
    /**
     * @var Mage_Webapi_Model_Resource_Acl_Role
     */
    protected $_roleResource;

    /**
     * @var Mage_Webapi_Model_Authorization_Role_Factory
     */
    protected $_roleFactory;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @param Mage_Webapi_Model_Resource_Acl_Role $roleResource
     * @param Mage_Webapi_Model_Authorization_Role_Factory $roleFactory
     */
    public function __construct(Mage_Webapi_Model_Resource_Acl_Role $roleResource,
        Mage_Webapi_Model_Authorization_Role_Factory $roleFactory
    ) {
        $this->_roleResource = $roleResource;
        $this->_roleFactory = $roleFactory;
    }

    /**
     * Populate ACL with roles from external storage.
     *
     * @param Magento_Acl $acl
     */
    public function populateAcl(Magento_Acl $acl)
    {
        $roleList = $this->_roleResource->getRolesIds();
        foreach ($roleList as $roleId) {
            /** @var $aclRole Mage_Webapi_Model_Authorization_Role */
            $aclRole = $this->_roleFactory->createRole(array($roleId));
            $acl->addRole($aclRole);
            //Deny all privileges to Role. Some of them could be allowed later by whitelist
            $acl->deny($aclRole);
        }
    }
}
