<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

/**
 * Class \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
 *
 */
class StoreResolver
{
    /**
     * Store manager instance.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Website code-to-ID
     *
     * @var array
     */
    protected $websiteCodeToId = [];

    /**
     * Website code to store code-to-ID pairs which it consists.
     *
     * @var array
     */
    protected $websiteCodeToStoreIds = [];

    /**
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $storeCodeToId = [];

    /**
     * Store ID to its website stores IDs.
     *
     * @var array
     */
    protected $storeIdToWebsiteStoreIds = [];

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Initialize website values.
     *
     * @return $this
     */
    protected function _initWebsites()
    {
        /** @var $website \Magento\Store\Model\Website */
        foreach ($this->storeManager->getWebsites() as $website) {
            $this->websiteCodeToId[$website->getCode()] = $website->getId();
            $this->websiteCodeToStoreIds[$website->getCode()] = array_flip($website->getStoreCodes());
        }
        return $this;
    }

    /**
     * @param null|string $code
     * @return array|string|null
     */
    public function getWebsiteCodeToId($code = null)
    {
        if (empty($this->websiteCodeToId)) {
            $this->_initWebsites();
        }
        if ($code) {
            return isset($this->websiteCodeToId[$code]) ? $this->websiteCodeToId[$code] : null;
        }
        return $this->websiteCodeToId;
    }

    /**
     * @param null|string $code
     * @return array|string|null
     */
    public function getWebsiteCodeToStoreIds($code = null)
    {
        if (empty($this->websiteCodeToStoreIds)) {
            $this->_initWebsites();
        }
        if ($code) {
            return isset($this->websiteCodeToStoreIds[$code]) ? $this->websiteCodeToStoreIds[$code] : null;
        }
        return $this->websiteCodeToStoreIds;
    }

    /**
     * Initialize stores hash.
     *
     * @return $this
     */
    protected function _initStores()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->storeCodeToId[$store->getCode()] = $store->getId();
            $this->storeIdToWebsiteStoreIds[$store->getId()] = $store->getWebsite()->getStoreIds();
        }
        return $this;
    }

    /**
     * @param null|string $code
     * @return array|string|null
     */
    public function getStoreCodeToId($code = null)
    {
        if (empty($this->storeCodeToId)) {
            $this->_initStores();
        }
        if ($code) {
            return isset($this->storeCodeToId[$code]) ? $this->storeCodeToId[$code] : null;
        }
        return $this->storeCodeToId;
    }

    /**
     * @param null|string $code
     * @return array|string|null
     */
    public function getStoreIdToWebsiteStoreIds($code = null)
    {
        if (empty($this->storeIdToWebsiteStoreIds)) {
            $this->_initStores();
        }
        if ($code) {
            return isset($this->storeIdToWebsiteStoreIds[$code]) ? $this->storeIdToWebsiteStoreIds[$code] : null;
        }
        return $this->storeIdToWebsiteStoreIds;
    }
}
