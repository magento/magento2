<?php
/**
 * Acl object factory.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Class \Magento\Framework\AclFactory
 *
 */
class AclFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new magento acl instance
     *
     * @return \Magento\Framework\Acl
     */
    public function create()
    {
        return $this->_objectManager->create(Acl::class);
    }
}
