<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

class HandlerFactory
{
    /**
     * Object manager
     *
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
     * Create handler instance
     *
     * @param string $instance
     * @param array $arguments
     * @return object
     * @throws \InvalidArgumentException
     */
    public function create($instance, array $arguments = [])
    {
        if (!is_subclass_of(
            $instance,
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface::class
        )
        ) {
            throw new \InvalidArgumentException(
                $instance .
                ' does not implement ' .
                \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface::class
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
