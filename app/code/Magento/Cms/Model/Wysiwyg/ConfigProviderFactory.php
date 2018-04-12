<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg;

/**
 * Class ConfigProviderFactory to create config provider object by class name
 */
class ConfigProviderFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

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
     * @return \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
     * @throws \InvalidArgumentException
     */
    public function create($instance, array $arguments = [])
    {
        if (!is_subclass_of(
            $instance,
            \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface::class
        )
        ) {
            throw new \InvalidArgumentException(
                $instance .
                ' does not implement ' .
                \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface::class
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
