<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Indexer\MultiDimensionProvider;

class DimensionCollectionFactory
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
     * @param \Magento\Framework\Indexer\MultiDimensionProviderFactory $multiDimensionProviderFactory
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     * @param array $dimensionProviders
     */
    public function __construct(
        \Magento\Framework\Indexer\MultiDimensionProviderFactory $multiDimensionProviderFactory,
        DimensionModeConfiguration $dimensionModeConfiguration,
        array $dimensionProviders
    ) {
        $this->multiDimensionProviderFactory = $multiDimensionProviderFactory;
        $this->dimensionProviders = $dimensionProviders;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
    }

    /**
     * Create MultiDimensionProvider for specified "dimension mode" - which dimensions indexer use for sharding
     *
     * @param string|null $dimensionsMode
     * @return MultiDimensionProvider
     */
    public function create(string $dimensionsMode = null): MultiDimensionProvider
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
