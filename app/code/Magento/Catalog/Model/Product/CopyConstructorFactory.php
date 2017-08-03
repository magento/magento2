<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

/**
 * Class \Magento\Catalog\Model\Product\CopyConstructorFactory
 *
 * @since 2.0.0
 */
class CopyConstructorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($instance, array $arguments = [])
    {
        if (!is_subclass_of($instance, \Magento\Catalog\Model\Product\CopyConstructorInterface::class)) {
            throw new \InvalidArgumentException(
                $instance . ' does not implement \Magento\Catalog\Model\Product\CopyConstructorInterface'
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
