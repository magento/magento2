<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

/**
 * Store manager interface
 *
 * @api
 * @since 2.0.0
 */
interface StoreManagerInterface
{
    /**
     * Store cache context
     */
    const CONTEXT_STORE = 'store';

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     * @since 2.0.0
     */
    public function setIsSingleStoreModeAllowed($value);

    /**
     * Check if store has only one store view
     *
     * @return bool
     * @since 2.0.0
     */
    public function hasSingleStore();

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSingleStoreMode();

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @since 2.0.0
     */
    public function getStore($storeId = null);

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Api\Data\StoreInterface[]
     * @since 2.0.0
     */
    public function getStores($withDefault = false, $codeKey = false);

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|\Magento\Store\Api\Data\WebsiteInterface $websiteId
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getWebsite($websiteId = null);

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     * @since 2.0.0
     */
    public function getWebsites($withDefault = false, $codeKey = false);

    /**
     * Reinitialize store list
     *
     * @return void
     * @since 2.0.0
     */
    public function reinitStores();

    /**
     * Retrieve default store for default group and website
     *
     * @return \Magento\Store\Api\Data\StoreInterface|null
     * @since 2.0.0
     */
    public function getDefaultStoreView();

    /**
     * Retrieve application store group object
     *
     * @param null|\Magento\Store\Api\Data\GroupInterface|string $groupId
     * @return \Magento\Store\Api\Data\GroupInterface
     * @since 2.0.0
     */
    public function getGroup($groupId = null);

    /**
     * Prepare array of store groups
     *
     * @param bool $withDefault
     * @return \Magento\Store\Api\Data\GroupInterface[]
     * @since 2.0.0
     */
    public function getGroups($withDefault = false);

    /**
     * Set current default store
     *
     * @param string $store
     * @return void
     * @since 2.0.0
     */
    public function setCurrentStore($store);
}
