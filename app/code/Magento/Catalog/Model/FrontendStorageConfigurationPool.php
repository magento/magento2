<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
     * @var array
     */
    private $storageConfigurations;

    /**
     * StorageConfigurationPool constructor.
     * @param array $storageConfigurations
     */
    public function __construct(array $storageConfigurations = [])
    {
        $this->storageConfigurations = $storageConfigurations;
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
        if (isset($this->storageConfigurations[$namespace])) {
            if (!$this->storageConfigurations[$namespace] instanceof FrontendStorageConfigurationInterface) {
                throw new LocalizedException(
                    __(sprintf("Invalid pool type with namespace: %s", $namespace))
                );
            }
        } else {
            return false;
        }

        return $this->storageConfigurations[$namespace];
    }
}
