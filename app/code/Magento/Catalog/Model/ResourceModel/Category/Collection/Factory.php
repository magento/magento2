<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * Class Factory
 * @deprecated 2.1.0
 * @since 2.0.0
 */
class Factory
{
    /**
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
     * Return newly created instance of the category collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @since 2.0.0
     */
    public function create()
    {
        return $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
    }
}
