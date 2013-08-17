<?php
/**
 * API ACL Resource Loader.
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
class Mage_Webapi_Model_Authorization_Loader_Resource extends Magento_Acl_Loader_Resource
{
    /**
     * Deny each resource for all roles.
     *
     * @param Magento_Acl $acl
     */
    protected function _denyResources(Magento_Acl $acl)
    {
        foreach ($acl->getResources() as $aclResource) {
            $acl->deny(null, $aclResource);
        }
    }

    /**
     * Load virtual resources as sub-resources of existing one.
     *
     * @param Magento_Acl $acl
     */
    protected function _loadVirtualResources(Magento_Acl $acl)
    {
        $virtualResources = $this->_configReader->getAclVirtualResources();
        /** @var $resourceConfig DOMElement */
        foreach ($virtualResources as $resourceConfig) {
            if (!($resourceConfig instanceof DOMElement)) {
                continue;
            }
            $parent = $resourceConfig->getAttribute('parent');
            $resourceId = $resourceConfig->getAttribute('id');
            if ($acl->has($parent) && !$acl->has($resourceId)) {
                /** @var $resource Magento_Acl_Resource */
                $resource = $this->_resourceFactory->createResource(array('resourceId' => $resourceId));
                $acl->addResource($resource, $parent);
            }
        }
    }

    /**
     * Populate ACL with resources from external storage.
     *
     * @param Magento_Acl $acl
     * @throws Mage_Core_Exception
     */
    public function populateAcl(Magento_Acl $acl)
    {
        parent::populateAcl($acl);
        $this->_denyResources($acl);
        $this->_loadVirtualResources($acl);
    }
}
