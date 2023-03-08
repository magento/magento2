<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\UrlInput;

use Magento\Framework\ObjectManagerInterface;

/**
 * Returns information about allowed links
 */
class LinksConfigProvider implements ConfigInterface
{
    /**
     * LinksProvider constructor.
     * @param array $linksConfiguration
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        private readonly array $linksConfiguration,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        $config = [];
        foreach ($this->linksConfiguration as $linkName => $className) {
            $config[$linkName] = $this->createConfigProvider($className)->getConfig();
        }
        return $config;
    }

    /**
     * Create config provider
     *
     * @param string $instance
     * @return ConfigInterface
     */
    private function createConfigProvider($instance): ConfigInterface
    {
        return $this->objectManager->create($instance);
    }
}
