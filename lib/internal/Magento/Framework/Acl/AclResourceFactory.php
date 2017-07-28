<?php
/**
 * Factory for Acl resource
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\Acl\AclResourceFactory
 *
 * @since 2.0.0
 */
class AclResourceFactory
{
    const RESOURCE_CLASS_NAME = \Magento\Framework\Acl\AclResource::class;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Return new ACL resource model
     *
     * @param array $arguments
     * @return AclResource
     * @since 2.0.0
     */
    public function createResource(array $arguments = [])
    {
        return $this->_objectManager->create(self::RESOURCE_CLASS_NAME, $arguments);
    }
}
