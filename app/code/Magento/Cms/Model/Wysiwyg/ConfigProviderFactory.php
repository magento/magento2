<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg;

use Magento\Framework\Data\Wysiwyg\ConfigProviderInterface as WysiwygConfigInterface;

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
     */
    public function create(string $instance, array $arguments = []): WysiwygConfigInterface
    {
        return $this->objectManager->create($instance, $arguments);
    }
}
