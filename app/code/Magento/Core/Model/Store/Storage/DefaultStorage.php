<?php
/**
 * Store loader
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Store\Storage;

class DefaultStorage implements \Magento\Core\Model\Store\StorageInterface
{
    /**
     * Application store object
     *
     * @var \Magento\Core\Model\Store
     */
    protected $_store;

    /**
     * Application website object
     *
     * @var \Magento\Core\Model\Website
     */
    protected $_website;

    /**
     * Application website object
     *
     * @var \Magento\Core\Model\Store\Group
     */
    protected $_group;

    /**
     * @param \Magento\Core\Model\StoreFactory $storeFactory
     * @param \Magento\Core\Model\Website\Factory $websiteFactory
     * @param \Magento\Core\Model\Store\Group\Factory $groupFactory
     */
    public function __construct(
        \Magento\Core\Model\StoreFactory $storeFactory,
        \Magento\Core\Model\Website\Factory $websiteFactory,
        \Magento\Core\Model\Store\Group\Factory $groupFactory
    ) {

        $this->_store = $storeFactory->create();
        $this->_store->setId(\Magento\Core\Model\Store::DISTRO_STORE_ID);
        $this->_store->setCode(\Magento\Core\Model\Store::DEFAULT_CODE);
        $this->_website = $websiteFactory->create();
        $this->_group = $groupFactory->create();
    }

    /**
     * Initialize current application store
     *
     * @return void
     */
    public function initCurrentStore()
    {
        //not applicable for default storage
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        //not applicable for default storage
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
     * Retrieve application store object
     *
     * @param null|string|bool|int|\Magento\Core\Model\Store $storeId
     * @return \Magento\Core\Model\Store
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
     * @return \Magento\Core\Model\Store[]
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return array();
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     * @return \Magento\Core\Model\Website
     * @throws \Magento\Model\Exception
     */
    public function getWebsite($websiteId = null)
    {
        if ($websiteId instanceof \Magento\Core\Model\Website) {
            return $websiteId;
        }

        return $this->_website;
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return \Magento\Core\Model\Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = array();

        if ($withDefault) {
            $key = $codeKey ? $this->_website->getCode() : $this->_website->getId();
            $websites[$key] = $this->_website;
        }

        return $websites;
    }

    /**
     * Retrieve application store group object
     *
     * @param null|\Magento\Core\Model\Store\Group|string $groupId
     * @return \Magento\Core\Model\Store\Group
     * @throws \Magento\Model\Exception
     */
    public function getGroup($groupId = null)
    {
        if ($groupId instanceof \Magento\Core\Model\Store\Group) {
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
     * @return \Magento\Core\Model\Store\Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        $groups = array();

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
        //not applicable for default storage
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return \Magento\Core\Model\Store|null
     */
    public function getDefaultStoreView()
    {
        return null;
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     * @return void
     */
    public function clearWebsiteCache($websiteId = null)
    {
        //not applicable for default storage
    }

    /**
     * Get either default or any store view
     *
     * @return \Magento\Core\Model\Store|null
     */
    public function getAnyStoreView()
    {
        return null;
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

    /**
     * @return void
     * @throws \Magento\Core\Model\Store\Exception
     */
    public function throwStoreException()
    {
        //not applicable for default storage
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getCurrentStore()
    {
        return $this->_store->getCode();
    }
}
