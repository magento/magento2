<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\DimensionProviderInterface;

class DimensionProviderFactory
{
    /**
     *
     */
    const EMPTY_CONFIGURATION = '-';

    /**
     * @var \Magento\Framework\Indexer\DimensionCollectionFactory
     */
    private $dimensionProviderFactory;

    /**
     * @var array
     */
    private $dataProviders;

    /**
     * @var array
     */
    private $modes;

    /**
     * @var array
     */
    private $modesConfiguration;

    public function __construct(
        \Magento\Framework\Indexer\DimensionProviderFactory $dimensionProviderFactory,
        array $dataProviders,
        array $modes,
        array $modesConfiguration
    ) {
        $this->dimensionProviderFactory = $dimensionProviderFactory;
        $this->dataProviders = $dataProviders;
        $this->modes = $modes;
        $this->modesConfiguration = $modesConfiguration;
    }

    public function createByMode($dimensionsMode): DimensionProviderInterface
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

        return $this->dimensionProviderFactory->create(
            [
                'dimensionDataProviders' => $this->getDataProviders($modeConfigurationKey)
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
            if (!array_key_exists($modeDataProviderName, $this->dataProviders)) {
                throw new \InvalidArgumentException(
                    sprintf('Missing data provider "%s".', $modeDataProviderName)
                );
            }

            $providers[] = $this->dataProviders[$modeDataProviderName]->create();
        }

        return $providers;
    }
}
