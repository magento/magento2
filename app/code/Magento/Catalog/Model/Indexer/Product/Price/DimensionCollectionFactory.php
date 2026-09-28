<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
     * Create MultiDimensionProvider for specified "dimension mode".
     *
     * By default return multiplication of dimensions by current set mode
     *
     * @param string|null $dimensionsMode
     * @return MultiDimensionProvider
     */
    public function create(?string $dimensionsMode = null): MultiDimensionProvider
    {
        $dimensionConfiguration = $this->dimensionModeConfiguration->getDimensionConfiguration($dimensionsMode);

        $providers = [];
        foreach ($dimensionConfiguration as $dimensionName) {
            if (!isset($this->dimensionProviders[$dimensionName])) {
                throw new \LogicException(
                    'Dimension Provider is missing. Cannot handle unknown dimension: ' . $dimensionName
                );
            }
            $providers[] = clone $this->dimensionProviders[$dimensionName];
        }

        return $this->multiDimensionProviderFactory->create(
            [
                'dimensionProviders' => $providers
            ]
        );
    }
}
