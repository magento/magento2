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
 * @since 2.0.0
 */
class AclFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new magento acl instance
     *
     * @return \Magento\Framework\Acl
     * @since 2.0.0
     */
    public function create()
    {
        return $this->_objectManager->create(Acl::class);
    }
}
