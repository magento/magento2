<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Collection;

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
     * @param string $className
     * @param array $data
     * @return AbstractCollection
     * @throws \InvalidArgumentException
     */
    public function create($className, array $data = [])
    {
        $instance = $this->_objectManager->create($className, $data);

        if (!$instance instanceof AbstractCollection) {
            throw new \InvalidArgumentException(
                $className . ' does not implement \Magento\Sales\Model\Resource\Order\Collection\AbstractCollection'
            );
        }
        return $instance;
    }
}
