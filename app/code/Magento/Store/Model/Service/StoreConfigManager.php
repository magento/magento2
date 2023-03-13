<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Service;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Model\Data\StoreConfig;
use Magento\Store\Model\Data\StoreConfigFactory;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class StoreConfigManager implements StoreConfigManagerInterface
{
    /**
     * Map the setters to config path
     *
     * @var array
     */
    protected $configPaths = [
        'setLocale' => 'general/locale/code',
        'setBaseCurrencyCode' => 'currency/options/base',
        'setDefaultDisplayCurrencyCode' => 'currency/options/default',
        'setTimezone' => 'general/locale/timezone',
        'setWeightUnit' => DirectoryHelper::XML_PATH_WEIGHT_UNIT
    ];

    /**
     * @param CollectionFactory $storeCollectionFactory
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param StoreConfigFactory $storeConfigFactory
     */
    public function __construct(
        protected readonly CollectionFactory $storeCollectionFactory,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly StoreConfigFactory $storeConfigFactory
    ) {
    }

    /**
     * Get store configurations
     *
     * @param string[] $storeCodes list of stores by store codes, will return all if storeCodes is not set
     * @return StoreConfigInterface[]
     */
    public function getStoreConfigs(array $storeCodes = null)
    {
        $storeConfigs = [];
        $storeCollection = $this->storeCollectionFactory->create();
        if ($storeCodes != null) {
            $storeCollection->addFieldToFilter('code', ['in' => $storeCodes]);
        }

        foreach ($storeCollection->load() as $item) {
            $storeConfigs[] = $this->getStoreConfig($item);
        }
        return $storeConfigs;
    }

    /**
     * Get store specific configs
     *
     * @param Store|StoreInterface $store
     * @return StoreConfigInterface
     */
    protected function getStoreConfig($store)
    {
        /** @var StoreConfig $storeConfig */
        $storeConfig = $this->storeConfigFactory->create();

        $storeConfig->setId($store->getId())
            ->setCode($store->getCode())
            ->setWebsiteId($store->getWebsiteId());

        foreach ($this->configPaths as $methodName => $configPath) {
            $configValue = $this->scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_STORES,
                $store->getCode()
            );
            $storeConfig->$methodName($configValue);
        }

        $storeConfig->setBaseUrl($store->getBaseUrl(UrlInterface::URL_TYPE_WEB, false));
        $storeConfig->setSecureBaseUrl($store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true));
        $storeConfig->setBaseLinkUrl($store->getBaseUrl(UrlInterface::URL_TYPE_LINK, false));
        $storeConfig->setSecureBaseLinkUrl($store->getBaseUrl(UrlInterface::URL_TYPE_LINK, true));
        $storeConfig->setBaseStaticUrl($store->getBaseUrl(UrlInterface::URL_TYPE_STATIC, false));
        $storeConfig->setSecureBaseStaticUrl(
            $store->getBaseUrl(UrlInterface::URL_TYPE_STATIC, true)
        );
        $storeConfig->setBaseMediaUrl($store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, false));
        $storeConfig->setSecureBaseMediaUrl(
            $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true)
        );
        return $storeConfig;
    }
}
