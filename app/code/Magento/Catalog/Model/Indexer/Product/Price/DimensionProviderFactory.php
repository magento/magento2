<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\MultiDimensionProviderInterface;

class DimensionProviderFactory
{
    /**
     *
     */
    const EMPTY_CONFIGURATION = '-';

    /**
     * @var \Magento\Framework\Indexer\MultiDimensionProviderFactory
     */
    private $multiDimensionProviderFactory;

    /**
     * @var array
     */
    private $dimensionProviders;

    /**
     * @var array
     */
    private $modes;

    /**
     * @var array
     */
    private $modesConfiguration;

    public function __construct(
        \Magento\Framework\Indexer\MultiDimensionProviderInterfaceFactory $multiDimensionProviderFactory,
        array $dimensionProviders,
        array $modes,
        array $modesConfiguration
    ) {
        $this->multiDimensionProviderFactory = $multiDimensionProviderFactory;
        $this->dimensionProviders = $dimensionProviders;
        $this->modes = $modes;
        $this->modesConfiguration = $modesConfiguration;
    }

    /**
     * Create MultiDimensionProviderInterface for specified "dimension mode" - which dimensions indexer use for sharding
     *
     * @param string $dimensionsMode
     * @return MultiDimensionProviderInterface
     */
    public function createByMode(string $dimensionsMode): MultiDimensionProviderInterface
    {
        if (!in_array($dimensionsMode, $this->modes)) {
            throw new \InvalidArgumentException(
                sprintf('Undefined dimension mode "%s".', $dimensionsMode)
            );
        }

        $modeConfigurationKey = array_search($dimensionsMode, $this->modes, true);
        if (!array_key_exists($modeConfigurationKey, $this->modesConfiguration)) {
            throw new \InvalidArgumentException(
                sprintf('Missing configuration for mode "%s".', $dimensionsMode)
            );
        }

        return $this->multiDimensionProviderFactory->create(
            [
                'dimensionProviders' => $this->getDataProviders($modeConfigurationKey)
            ]
        );
    }

    private function getDataProviders($modeConfigurationKey): array
    {
        $modeConfiguration = $this->modesConfiguration[$modeConfigurationKey];
        $providers = [];

        if ($modeConfiguration === self::EMPTY_CONFIGURATION) {
            return $providers;
        }

        foreach ($modeConfiguration as $modeDataProviderName) {
            if (!array_key_exists($modeDataProviderName, $this->dimensionProviders)) {
                throw new \InvalidArgumentException(
                    sprintf('Missing data provider "%s".', $modeDataProviderName)
                );
            }

            $providers[] = clone $this->dimensionProviders[$modeDataProviderName];
        }

        return $providers;
    }
}
