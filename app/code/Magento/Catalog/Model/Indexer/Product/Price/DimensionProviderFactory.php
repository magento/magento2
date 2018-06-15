<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Indexer\MultiDimensionProviderInterface;

class DimensionProviderFactory
{
    /**
     * @var \Magento\Framework\Indexer\MultiDimensionProviderFactory
     */
    private $multiDimensionProviderFactory;

    /**
     * @var DimensionProviderInterface[]
     */
    private $dimensionProviders;

    /**
     * @var DimensionModeConfiguration
     */
    private $dimensionModeConfiguration;

    /**
     * @param \Magento\Framework\Indexer\MultiDimensionProviderInterfaceFactory $multiDimensionProviderFactory
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     * @param array $dimensionProviders
     */
    public function __construct(
        \Magento\Framework\Indexer\MultiDimensionProviderInterfaceFactory $multiDimensionProviderFactory,
        DimensionModeConfiguration $dimensionModeConfiguration,
        array $dimensionProviders
    ) {
        $this->multiDimensionProviderFactory = $multiDimensionProviderFactory;
        $this->dimensionProviders = $dimensionProviders;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
    }

    /**
     * Create MultiDimensionProviderInterface for specified "dimension mode" - which dimensions indexer use for sharding
     *
     * @param string|null $dimensionsMode
     * @return MultiDimensionProviderInterface
     */
    public function createByMode(string $dimensionsMode = null): MultiDimensionProviderInterface
    {
        $dimensionConfiguration = $this->dimensionModeConfiguration->getDimensionConfiguration($dimensionsMode);
        $dimensionProvidersMap = [];
        foreach ($this->dimensionProviders as $dimensionProvider) {
            // TODO: fix hac by getDimensionName?
            $dimensionProvidersMap[$dimensionProvider::DIMENSION_NAME] = $dimensionProvider;
        }

        $providers = [];
        foreach ($dimensionConfiguration as $dimensionName) {
            if (!isset($dimensionProvidersMap[$dimensionName])) {
                throw new \InvalidArgumentException(
                    sprintf('Missing data provider for Dimension with name "%s".', $dimensionName)
                );
            }
            $providers[] = clone $dimensionProvidersMap[$dimensionName];
        }

        return $this->multiDimensionProviderFactory->create(
            [
                'dimensionProviders' => $providers
            ]
        );
    }
}
