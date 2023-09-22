<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\TableNotFoundException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Store\AllStoresCollectionFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Website\AllWebsitesCollection;
use Magento\Store\Model\ResourceModel\Website\AllWebsitesCollectionFactory;

/**
 * Fallback through different scopes and merge them
 */
class Fallback implements PostProcessorInterface, ResetAfterRequestInterface
{
    /**
     * @var Scopes
     */
    private $scopes;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $storeData = [];

    /**
     * @var array
     */
    private $websiteData = [];

    /**
     * @var Store
     */
    private $storeResource;

    /**
     * @var Website
     */
    private $websiteResource;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var array
     */
    private $websiteNonStdCodes = [];

    /**
     * @var array
     */
    private $storeNonStdCodes = [];

    /**
     * Fallback constructor.
     *
     * @param Scopes $scopes
     * @param ResourceConnection $resourceConnection
     * @param Store $storeResource
     * @param Website $websiteResource
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Scopes             $scopes,
        ResourceConnection $resourceConnection,
        Store              $storeResource,
        Website            $websiteResource,
        DeploymentConfig   $deploymentConfig
    ) {
        $this->scopes = $scopes;
        $this->resourceConnection = $resourceConnection;
        $this->storeResource = $storeResource;
        $this->websiteResource = $websiteResource;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function process(array $data)
    {
        $this->loadScopes();

        $defaultConfig = isset($data['default']) ? $data['default'] : [];
        $result = [
            'default' => $defaultConfig,
            'websites' => [],
            'stores' => []
        ];

        $websitesConfig = isset($data['websites']) ? $data['websites'] : [];
        $result['websites'] = $this->prepareWebsitesConfig($defaultConfig, $websitesConfig);

        $storesConfig = isset($data['stores']) ? $data['stores'] : [];
        $result['stores'] = $this->prepareStoresConfig($defaultConfig, $websitesConfig, $storesConfig);

        return $result;
    }

    /**
     * Prepare website data from Config/Type/Scopes
     *
     * @param array $defaultConfig
     * @param array $websitesConfig
     * @return array
     */
    private function prepareWebsitesConfig(
        array $defaultConfig,
        array $websitesConfig
    ) {
        $result = [];
        foreach ((array)$this->websiteData as $website) {
            $code = $website['code'];
            $id = $website['website_id'];
            $websiteConfig = $this->mapEnvWebsiteToWebsite($websitesConfig, $code);
            $result[$code] = array_replace_recursive($defaultConfig, $websiteConfig);
            $result[$id] = $result[$code];
        }
        return $result;
    }

    /**
     * Prepare stores data from Config/Type/Scopes
     *
     * @param array $defaultConfig
     * @param array $websitesConfig
     * @param array $storesConfig
     * @return array
     */
    private function prepareStoresConfig(
        array $defaultConfig,
        array $websitesConfig,
        array $storesConfig
    ) {
        $result = [];

        foreach ((array)$this->storeData as $store) {
            $code = $store['code'];
            $id = $store['store_id'];
            $websiteConfig = [];
            if (isset($store['website_id'])) {
                $websiteConfig = $this->getWebsiteConfig($websitesConfig, $store['website_id']);
            }
            $storeConfig = $this->mapEnvStoreToStore($storesConfig, $code);
            $result[$code] = array_replace_recursive($defaultConfig, $websiteConfig, $storeConfig);
            $result[strtolower($code)] = $result[$code];
            $result[$id] = $result[$code];
        }
        return $result;
    }

    /**
     * Find information about website by its ID.
     *
     * @param array $websites Has next format: (website_code => [website_data])
     * @param int $id
     * @return array
     */
    private function getWebsiteConfig(array $websites, $id)
    {
        foreach ((array)$this->websiteData as $website) {
            if ($website['website_id'] == $id) {
                $code = $website['code'];
                $nonStdConfigs = $this->getTheEnvConfigs($websites, $this->websiteNonStdCodes, $code);
                $stdConfigs = $websites[$code] ?? [];
                return count($nonStdConfigs) ? $stdConfigs + $nonStdConfigs : $stdConfigs;
            }
        }
        return [];
    }

    /**
     * Map $_ENV lower cased store codes to upper-cased and camel cased store codes to get the proper configuration
     *
     * @param array $configs
     * @param string $code
     * @return array
     */
    private function mapEnvStoreToStore(array $configs, string $code): array
    {
        if (!count($this->storeNonStdCodes)) {
            $this->storeNonStdCodes = array_diff(array_keys($configs), array_column($this->storeData, 'code'));
        }

        return $this->getTheEnvConfigs($configs, $this->storeNonStdCodes, $code);
    }

    /**
     * Map $_ENV lower cased website codes to upper-cased and camel cased website codes to get the proper configuration
     *
     * @param array $configs
     * @param string $code
     * @return array
     */
    private function mapEnvWebsiteToWebsite(array $configs, string $code): array
    {
        if (!count($this->websiteNonStdCodes)) {
            $this->websiteNonStdCodes = array_diff(array_keys($configs), array_keys($this->websiteData));
        }

        return $this->getTheEnvConfigs($configs, $this->websiteNonStdCodes, $code);
    }

    /**
     * Get all $_ENV configs from non-matching store/website codes
     *
     * @param array $configs
     * @param array $nonStdCodes
     * @param string $code
     * @return array
     */
    private function getTheEnvConfigs(array $configs, array $nonStdCodes, string $code): array
    {
        $additionalConfigs = [];
        foreach ($nonStdCodes as $nonStdStoreCode) {
            if (strtolower($nonStdStoreCode) === strtolower($code)) {
                $additionalConfigs = $this->getConfigsByNonStandardCodes($configs, $nonStdStoreCode, $code);
            }
        }

        return count($additionalConfigs) ? $additionalConfigs : ($configs[$code] ?? []);
    }

    /**
     * Match non-standard website/store codes with internal codes
     *
     * @param array $configs
     * @param string $nonStdCode
     * @param string $internalCode
     * @return array
     */
    private function getConfigsByNonStandardCodes(array $configs, string $nonStdCode, string $internalCode): array
    {
        $internalCodeConfigs = $configs[$internalCode] ?? [];
        if (strtolower($internalCode) === strtolower($nonStdCode)) {
            return isset($configs[$nonStdCode]) ?
                $internalCodeConfigs + $configs[$nonStdCode]
                : $internalCodeConfigs;
        }
        return $internalCodeConfigs;
    }

    /**
     * Load config from database.
     *
     * @return void
     */
    private function loadScopes(): void
    {
        try {
            if ($this->deploymentConfig->isDbAvailable()) {
                $this->storeData = $this->storeResource->readAllStores();
                $this->websiteData = $this->websiteResource->readAllWebsites();
            } else {
                $this->storeData = $this->scopes->get('stores');
                $this->websiteData = $this->scopes->get('websites');
            }
        } catch (TableNotFoundException $exception) {
            // database is empty or not setup
            $this->storeData = [];
            $this->websiteData = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->storeData = [];
        $this->websiteData = [];
    }
}
