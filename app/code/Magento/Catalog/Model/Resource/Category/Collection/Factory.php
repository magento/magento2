<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Category\Collection;

class Factory
{
    /**
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
     * Return newly created instance of the category collection
     *
     * @return \Magento\Catalog\Model\Resource\Category\Collection
     */
    public function create()
    {
        return $this->_objectManager->create('Magento\Catalog\Model\Resource\Category\Collection');
    }
}
