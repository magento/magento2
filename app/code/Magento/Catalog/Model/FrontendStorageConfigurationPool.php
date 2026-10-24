<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * This pool allows to collect all dynamic data, as settings and provide this data on frontend
 * Pool of different storage configuration, which retrieve all dynamic configurations to frontend storage manager
 * Each configuration object should have \Magento\Catalog\Model\FrontendStorageConfigurationInterface interface
 * Each configuration object provide only dynamic settings. For example, from Stores Configurations
 * All configurations will be used in front
 */
class FrontendStorageConfigurationPool
{
    /**
     * StorageConfigurationPool constructor.
     * @param array $storageConfigurations
     */
    public function __construct(private array $storageConfigurations = [])
    {
    }

    /**
     * Retrieve storage collector (which hold dynamic configurations) by its namespace
     *
     * @param string $namespace
     * @return FrontendStorageConfigurationInterface | bool
     * @throws LocalizedException
     */
    public function get($namespace)
    {
        $storageConfiguration = $this->storageConfigurations[$namespace] ?? null;
        if ($storageConfiguration === null) {
            return false;
        }
        if (!$storageConfiguration instanceof FrontendStorageConfigurationInterface) {
            throw new LocalizedException(
                __("Invalid pool type with namespace: %1", $namespace)
            );
        }

        return $storageConfiguration;
    }
}
