<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Store\AllStoresCollectionFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Website\AllWebsitesCollection;
use Magento\Store\Model\ResourceModel\Website\AllWebsitesCollectionFactory;

/**
 * Fallback through different scopes and merge them
 * @since 2.1.3
 */
class Fallback implements PostProcessorInterface
{
    /**
     * @var Scopes
     * @since 2.1.3
     */
    private $scopes;

    /**
     * @var ResourceConnection
     * @since 2.1.3
     */
    private $resourceConnection;

    /**
     * @var array
     * @since 2.1.3
     */
    private $storeData = [];

    /**
     * @var array
     * @since 2.1.3
     */
    private $websiteData = [];

    /**
     * @var Store
     * @since 2.1.3
     */
    private $storeResource;

    /**
     * @var Website
     * @since 2.1.3
     */
    private $websiteResource;

    /**
     * @var DeploymentConfig
     * @since 2.1.3
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
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function process(array $data)
    {
        if ($this->deploymentConfig->isDbAvailable()) {//read only from db
            $this->storeData = $this->storeResource->readAllStores();
            $this->websiteData = $this->websiteResource->readAllWebsites();
        } else {
            $this->storeData = $this->scopes->get('stores');
            $this->websiteData = $this->scopes->get('websites');
        }

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
     * @since 2.1.3
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
     * @since 2.1.3
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
     * @since 2.1.3
     */
    private function getWebsiteConfig(array $websites, $id)
    {
        foreach ((array)$this->websiteData as $website) {
            if ($website['website_id'] == $id) {
                $code = $website['code'];
                return isset($websites[$code]) ? $websites[$code] : [];
            }
        }
        return [];
    }
}
