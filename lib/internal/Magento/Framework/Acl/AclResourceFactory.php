<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for Acl resource
 *
 * @api
 */
class AclResourceFactory
{
    const RESOURCE_CLASS_NAME = \Magento\Framework\Acl\AclResource::class;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
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
     */
    public function createResource(array $arguments = [])
    {
        return $this->_objectManager->create(self::RESOURCE_CLASS_NAME, $arguments);
    }
}
