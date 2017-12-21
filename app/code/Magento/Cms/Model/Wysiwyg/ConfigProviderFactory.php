<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Wysiwyg;

/**
 * Class ConfigProviderFactory
 * @package Magento\Cms\Model\Wysiwyg
 */
class ConfigProviderFactory
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
     * Create config provider instance
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
            \Magento\Config\Model\Wysiwyg\ConfigInterface::class
        )
        ) {
            throw new \InvalidArgumentException(
                $instance .
                ' does not implement ' .
                \Magento\Config\Model\Wysiwyg\ConfigInterface::class
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
