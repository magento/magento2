<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

class ColorModesProvider
{
    private $colorModes;

    private $objectManager;

    public function __construct(
        array $colorModesPool,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->colorModes = $colorModesPool;
        $this->objectManager = $objectManager;
    }

    public function getModes()
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
     * @return ConfigInterface
     */
    private function createModeProvider($instance)
    {
        if (!is_subclass_of(
            $instance,
            ModeInterface::class
        )
        ) {
            throw new \InvalidArgumentException(
                $instance .
                ' does not implement ' .
                ModeInterface::class
            );
        }
        return $this->objectManager->create($instance);
    }
}
