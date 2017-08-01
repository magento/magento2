<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Collection;

/**
 * Class \Magento\Sales\Model\ResourceModel\Order\Collection\Factory
 *
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
     * @param string $className
     * @param array $data
     * @return AbstractCollection
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($className, array $data = [])
    {
        $instance = $this->_objectManager->create($className, $data);

        if (!$instance instanceof AbstractCollection) {
            throw new \InvalidArgumentException(
                $className .
                ' does not implement \Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection'
            );
        }
        return $instance;
    }
}
