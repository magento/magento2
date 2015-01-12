<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Resource\Report\Collection;

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
     * Create collection instance
     *
     * @param string $className
     * @param array $arguments
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function create($className, array $arguments = [])
    {
        return $this->_objectManager->create($className, $arguments);
    }
}
