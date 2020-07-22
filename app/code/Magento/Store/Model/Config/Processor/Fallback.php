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
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Store\AllStoresCollectionFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Website\AllWebsitesCollection;
use Magento\Store\Model\ResourceModel\Website\AllWebsitesCollectionFactory;

/**
 * Fallback through different scopes and merge them
 */
class Fallback implements PostProcessorInterface
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
     * Fallback constructor.
     *
     * @param Scopes $scopes
     * @param ResourceConnection $resourceConnection
     * @param Store $storeResource
     * @param Website $websiteResource
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Scopes $scopes,
        ResourceConnection $resourceConnection,
        Store $storeResource,
        Website $websiteResource,
        DeploymentConfig $deploymentConfig
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
            $websiteConfig = isset($websitesConfig[$code]) ? $websitesConfig[$code] : [];
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
            $storeConfig = isset($storesConfig[$code]) ? $storesConfig[$code] : [];
            $result[$code] = array_replace_recursive($defaultConfig, $websiteConfig, $storeConfig);
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
                return $websites[$code] ?? [];
            }
        }
        return [];
    }

    /**
     * Load config from database.
     *
     * @return void
     */
    private function loadScopes(): void
    {
        $loaded = false;
        try {
            if ($this->deploymentConfig->isDbAvailable()) {
                $this->storeData = $this->storeResource->readAllStores();
                $this->websiteData = $this->websiteResource->readAllWebsites();
                $loaded = true;
            }
        } catch (TableNotFoundException $exception) {
            // database is empty or not setup
            $loaded = false;
        }
        if (!$loaded) {
            $this->storeData = $this->scopes->get('stores');
            $this->websiteData = $this->scopes->get('websites');
        }
    }
}
