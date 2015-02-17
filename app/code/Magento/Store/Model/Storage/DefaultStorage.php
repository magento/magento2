<?php
/**
 * Store loader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Storage;

class DefaultStorage implements \Magento\Store\Model\StoreManagerInterface
{
    /**
     * Application store object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Application website object
     *
     * @var \Magento\Store\Model\Website
     */
    protected $_website;

    /**
     * Application website object
     *
     * @var \Magento\Store\Model\Group
     */
    protected $_group;

    /**
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $groupFactory
    ) {
        $this->_store = $storeFactory->create();
        $this->_store->setId(\Magento\Store\Model\Store::DISTRO_STORE_ID);
        $this->_store->setCode(\Magento\Store\Model\Store::DEFAULT_CODE);
        $this->_website = $websiteFactory->create();
        $this->_group = $groupFactory->create();
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     */
    public function setIsSingleStoreModeAllowed($value)
    {
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore()
    {
        return false;
    }

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getStore($storeId = null)
    {
        return $this->_store;
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Model\Store[]
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return [];
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|\Magento\Store\Model\Website $websiteId
     * @return \Magento\Store\Model\Website
     * @throws \Magento\Framework\Model\Exception
     */
    public function getWebsite($websiteId = null)
    {
        if ($websiteId instanceof \Magento\Store\Model\Website) {
            return $websiteId;
        }

        return $this->_website;
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return \Magento\Store\Model\Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = [];

        if ($withDefault) {
            $key = $codeKey ? $this->_website->getCode() : $this->_website->getId();
            $websites[$key] = $this->_website;
        }

        return $websites;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup($groupId = null)
    {
        if ($groupId instanceof \Magento\Store\Model\Group) {
            return $groupId;
        }

        return $this->_group;
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Model\Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        $groups = [];

        if ($withDefault) {
            $key = $codeKey ? $this->_group->getCode() : $this->_group->getId();
            $groups[$key] = $this->_group;
        }
        return $groups;
    }

    /**
     * Reinitialize store list
     *
     * @return void
     */
    public function reinitStores()
    {
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return \Magento\Store\Model\Store|null
     */
    public function getDefaultStoreView()
    {
        return null;
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|\Magento\Store\Model\Website $websiteId
     * @return void
     */
    public function clearWebsiteCache($websiteId = null)
    {
    }

    /**
     * Set current default store
     *
     * @param string $store
     * @return void
     */
    public function setCurrentStore($store)
    {
    }
}
