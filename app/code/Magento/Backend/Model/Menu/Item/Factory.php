<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Item;

/**
 * @api
 * @since 100.0.2
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Backend\Model\Menu\Item::class, ['data' => $data]);
    }
}
