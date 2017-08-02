<?php
/**
 * ACL Resource Loader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Loader;

use Magento\Framework\Acl;
use Magento\Framework\Acl\AclResource as AclResource;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\AclResourceFactory;

/**
 * Class \Magento\Framework\Acl\Loader\ResourceLoader
 *
 * @since 2.0.0
 */
class ResourceLoader implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * Acl resource config
     *
     * @var ProviderInterface $resourceProvider
     * @since 2.0.0
     */
    protected $_resourceProvider;

    /**
     * Resource factory
     *
     * @var AclResourceFactory
     * @since 2.0.0
     */
    protected $_resourceFactory;

    /**
     * @param ProviderInterface $resourceProvider
     * @param AclResourceFactory $resourceFactory
     * @since 2.0.0
     */
    public function __construct(ProviderInterface $resourceProvider, AclResourceFactory $resourceFactory)
    {
        $this->_resourceProvider = $resourceProvider;
        $this->_resourceFactory = $resourceFactory;
    }

    /**
     * Populate ACL with resources from external storage
     *
     * @param Acl $acl
     * @return void
     * @since 2.0.0
     */
    public function populateAcl(Acl $acl)
    {
        $this->_addResourceTree($acl, $this->_resourceProvider->getAclResources(), null);
    }

    /**
     * Add list of nodes and their children to acl
     *
     * @param Acl $acl
     * @param array $resources
     * @param AclResource $parent
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function _addResourceTree(Acl $acl, array $resources, AclResource $parent = null)
    {
        foreach ($resources as $resourceConfig) {
            if (!isset($resourceConfig['id'])) {
                throw new \InvalidArgumentException('Missing ACL resource identifier');
            }
            /** @var $resource AclResource */
            $resource = $this->_resourceFactory->createResource(['resourceId' => $resourceConfig['id']]);
            $acl->addResource($resource, $parent);
            if (isset($resourceConfig['children'])) {
                $this->_addResourceTree($acl, $resourceConfig['children'], $resource);
            }
        }
    }
}
