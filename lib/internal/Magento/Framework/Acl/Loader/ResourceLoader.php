<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Loader;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException as AclInvalidArgumentException;
use Magento\Framework\Acl;
use Magento\Framework\Acl\AclResource;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\AclResourceFactory;

/**
 * ACL Resource Loader
 */
class ResourceLoader implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * Acl resource config
     *
     * @var ProviderInterface
     */
    protected $_resourceProvider;

    /**
     * @var AclResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @param ProviderInterface $resourceProvider
     * @param AclResourceFactory $resourceFactory
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
     * @throws AclInvalidArgumentException
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
     * @throws AclInvalidArgumentException
     */
    protected function _addResourceTree(Acl $acl, array $resources, ?AclResource $parent = null)
    {
        foreach ($resources as $resourceConfig) {
            if (!isset($resourceConfig['id'])) {
                throw new \InvalidArgumentException('Missing ACL resource identifier');
            }
            $resource = $this->_resourceFactory->createResource(['resourceId' => $resourceConfig['id']]);
            $acl->addResource($resource, $parent);
            if (isset($resourceConfig['children'])) {
                $this->_addResourceTree($acl, $resourceConfig['children'], $resource);
            }
        }
    }
}
