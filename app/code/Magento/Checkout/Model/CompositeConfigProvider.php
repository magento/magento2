<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

/**
 * Composite checkout configuration provider.
 *
 * @see \Magento\Checkout\Model\ConfigProviderInterface
 * @api
 * @since 2.0.0
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigProviderInterface[]
     * @since 2.0.0
     */
    private $configProviders;

    /**
     * @param ConfigProviderInterface[] $configProviders
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        array $configProviders
    ) {
        $this->configProviders = $configProviders;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        return $config;
    }
}
