<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Collect all modes by configuration
 */
class ColorModesProvider
{
    /**
     * Stores color picker modes configuration
     *
     * @var array
     */
    private $colorModes;

    /**
     * Magento object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param array $colorModesPool
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        array $colorModesPool,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->colorModes = $colorModesPool;
        $this->objectManager = $objectManager;
    }

    /**
     * Return all available modes and their configuration
     *
     * @return array
     */
    public function getModes(): array
    {
        $config = [];
        foreach ($this->colorModes as $modeName => $className) {
            $config[$modeName] = $this->createModeProvider($className)->getConfig();
        }

        return $config;
    }

    /**
     * Create mode provider
     *
     * @param string $instance
     * @return ModeInterface
     */
    private function createModeProvider(string $instance): ModeInterface
    {
        return $this->objectManager->create($instance);
    }
}
