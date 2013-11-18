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
namespace Magento\Webapi\Model\Authorization\Loader;

/**
 * Class \Magento\Webapi\Model\Authorization\Loader\Resource
 */
class Resource extends \Magento\Acl\Loader\Resource
{
    /**
     * @param \Magento\Webapi\Model\Acl\Resource\ProviderInterface $resourceProvider
     * @param \Magento\Acl\ResourceFactory $resourceFactory
     */
    public function __construct(
        \Magento\Webapi\Model\Acl\Resource\ProviderInterface $resourceProvider,
        \Magento\Acl\ResourceFactory $resourceFactory
    ) {
        parent::__construct($resourceProvider, $resourceFactory);
    }

    /**
     * Deny each resource for all roles.
     *
     * @param \Magento\Acl $acl
     */
    protected function _denyResources(\Magento\Acl $acl)
    {
        foreach ($acl->getResources() as $aclResource) {
            $acl->deny(null, $aclResource);
        }
    }

    /**
     * Load virtual resources as sub-resources of existing one.
     *
     * @param \Magento\Acl $acl
     */
    protected function _loadVirtualResources(\Magento\Acl $acl)
    {
        $virtualResources = $this->_resourceProvider->getAclVirtualResources();
        foreach ($virtualResources as $virtualResource) {
            $resourceParent = $virtualResource['parent'];
            $resourceId = $virtualResource['id'];
            if ($acl->has($resourceParent) && !$acl->has($resourceId)) {
                /** @var $resource \Magento\Acl\Resource */
                $resource = $this->_resourceFactory->createResource(array('resourceId' => $resourceId));
                $acl->addResource($resource, $resourceParent);
            }
        }
    }

    /**
     * Populate ACL with resources from external storage.
     *
     * @param \Magento\Acl $acl
     * @throws \Magento\Core\Exception
     */
    public function populateAcl(\Magento\Acl $acl)
    {
        parent::populateAcl($acl);
        $this->_denyResources($acl);
        $this->_loadVirtualResources($acl);
    }
}
