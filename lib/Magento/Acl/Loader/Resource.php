<?php
/**
 * ACL Resource Loader
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
namespace Magento\Acl\Loader;

class Resource implements \Magento\Acl\LoaderInterface
{
    /**
     * Acl resource config
     *
     * @var \Magento\Acl\Resource\ProviderInterface $resourceProvider
     */
    protected $_resourceProvider;

    /**
     * Resource factory
     *
     * @var \Magento\Acl\ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @param \Magento\Acl\Resource\ProviderInterface $resourceProvider
     * @param \Magento\Acl\ResourceFactory $resourceFactory
     */
    public function __construct(
        \Magento\Acl\Resource\ProviderInterface $resourceProvider,
        \Magento\Acl\ResourceFactory $resourceFactory
    ) {
        $this->_resourceProvider = $resourceProvider;
        $this->_resourceFactory = $resourceFactory;
    }

    /**
     * Populate ACL with resources from external storage
     *
     * @param \Magento\Acl $acl
     */
    public function populateAcl(\Magento\Acl $acl)
    {
        $this->_addResourceTree($acl, $this->_resourceProvider->getAclResources(), null);
    }

    /**
     * Add list of nodes and their children to acl
     *
     * @param \Magento\Acl $acl
     * @param array $resources
     * @param \Magento\Acl\Resource $parent
     * @throws \InvalidArgumentException
     */
    protected function _addResourceTree(\Magento\Acl $acl, array $resources, \Magento\Acl\Resource $parent = null)
    {
        foreach ($resources as $resourceConfig) {
            if (!isset($resourceConfig['id'])) {
                throw new \InvalidArgumentException('Missing ACL resource identifier');
            }
            /** @var $resource \Magento\Acl\Resource */
            $resource = $this->_resourceFactory->createResource(array('resourceId' => $resourceConfig['id']));
            $acl->addResource($resource, $parent);
            if (isset($resourceConfig['children'])) {
                $this->_addResourceTree($acl, $resourceConfig['children'], $resource);
            }
        }
    }
}
