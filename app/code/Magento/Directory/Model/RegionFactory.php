<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

/**
 * @api
 * @since 2.0.0
 */
class RegionFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new region model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Region
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Directory\Model\Region::class, $arguments);
    }
}
