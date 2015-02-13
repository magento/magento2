<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

interface StoreManagerInterface
{
    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     */
    public function setIsSingleStoreModeAllowed($value);

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore();

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode();

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return \Magento\Store\Model\Store
     */
    public function getStore($storeId = null);

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Model\Store[]
     */
    public function getStores($withDefault = false, $codeKey = false);

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|\Magento\Store\Model\Website $websiteId
     * @return \Magento\Store\Model\Website
     * @throws \Magento\Framework\Model\Exception
     */
    public function getWebsite($websiteId = null);

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return \Magento\Store\Model\Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false);

    /**
     * Reinitialize store list
     *
     * @return void
     */
    public function reinitStores();

    /**
     * Retrieve default store for default group and website
     *
     * @return \Magento\Store\Model\Store|null
     */
    public function getDefaultStoreView();

    /**
     * Retrieve application store group object
     *
     * @param null|\Magento\Store\Model\Group|string $groupId
     * @return \Magento\Store\Model\Group
     */
    public function getGroup($groupId = null);

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Model\Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false);

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|\Magento\Store\Model\Website $websiteId
     * @return void
     */
    public function clearWebsiteCache($websiteId = null);

    /**
     * Set current default store
     *
     * @param string $store
     * @return void
     */
    public function setCurrentStore($store);
}
