<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * Class Factory
 * @deprecated
 */
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
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function create()
    {
        return $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
    }
}
