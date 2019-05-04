<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\UrlInput;

/**
 * Returns information about allowed links
 */
class LinksConfigProvider implements ConfigInterface
{
    /**
     * @var array
     */
    private $linksConfiguration;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * LinksProvider constructor.
     * @param array $linksConfiguration
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        array $linksConfiguration,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->linksConfiguration = $linksConfiguration;
        $this->objectManager = $objectManager;
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
