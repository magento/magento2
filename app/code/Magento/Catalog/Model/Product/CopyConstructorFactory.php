<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

class CopyConstructorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create link builder instance
     *
     * @param string $instance
     * @param array $arguments
     * @return CopyConstructorInterface
     * @throws \InvalidArgumentException
     */
    public function create($instance, array $arguments = [])
    {
        if (!is_subclass_of($instance, '\Magento\Catalog\Model\Product\CopyConstructorInterface')) {
            throw new \InvalidArgumentException(
                $instance . ' does not implement \Magento\Catalog\Model\Product\CopyConstructorInterface'
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
