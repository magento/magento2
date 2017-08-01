<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Item;

/**
 * @api
 * @since 2.0.0
 */
class Factory
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
     * Create menu item from array
     *
     * @param array $data
     * @return \Magento\Backend\Model\Menu\Item
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Backend\Model\Menu\Item::class, ['data' => $data]);
    }
}
