<?php
/**
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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core Website model
 *
 * @method Mage_Core_Model_Resource_Website _getResource()
 * @method Mage_Core_Model_Resource_Website getResource()
 * @method Mage_Core_Model_Website setCode(string $value)
 * @method string getName()
 * @method string getGroupTitle()
 * @method string getStoreTitle()
 * @method string getStoreId()
 * @method string getGroupId()
 * @method Mage_Core_Model_Website setName(string $value)
 * @method int getSortOrder()
 * @method Mage_Core_Model_Website setSortOrder(int $value)
 * @method Mage_Core_Model_Website setDefaultGroupId(int $value)
 * @method int getIsDefault()
 * @method Mage_Core_Model_Website setIsDefault(int $value)
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Core_Model_Website extends Mage_Core_Model_Abstract
{
    const ENTITY    = 'core_website';
    const CACHE_TAG = 'website';
    protected $_cacheTag = true;

    /**
     * @var string
     */
    protected $_eventPrefix = 'website';

    /**
     * @var string
     */
    protected $_eventObject = 'website';

    /**
     * Cache configuration array
     *
     * @var array
     */
    protected $_configCache = array();

    /**
     * Website Group Collection array
     *
     * @var array
     */
    protected $_groups;

    /**
     * Website group ids array
     *
     * @var array
     */
    protected $_groupIds = array();

    /**
     * The number of groups in a website
     *
     * @var int
     */
    protected $_groupsCount;

    /**
     * Website Store collection array
     *
     * @var array
     */
    protected $_stores;

    /**
     * Website store ids array
     *
     * @var array
     */
    protected $_storeIds = array();

    /**
     * Website store codes array
     *
     * @var array
     */
    protected $_storeCodes = array();

    /**
     * The number of stores in a website
     *
     * @var int
     */
    protected $_storesCount = 0;

    /**
     * Website default group
     *
     * @var Mage_Core_Model_Store_Group
     */
    protected $_defaultGroup;

    /**
     * Website default store
     *
     * @var Mage_Core_Model_Store
     */
    protected $_defaultStore;

    /**
     * is can delete website
     *
     * @var bool
     */
    protected $_isCanDelete;

    /**
     * @var bool
     */
    private $_isReadOnly = false;

    /**
     * init model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Website');
    }

    /**
     * Custom load
     *
     * @param int|string $id
     * @param string $field
     * @return Mage_Core_Model_Website
     */
    public function load($id, $field = null)
    {
        if (!is_numeric($id) && is_null($field)) {
            $this->_getResource()->load($this, $id, 'code');
            return $this;
        }
        return parent::load($id, $field);
    }

    /**
     * Load website configuration
     *
     * @param   string $code
     * @return  Mage_Core_Model_Website
     */
    public function loadConfig($code)
    {
        if (!Mage::getConfig()->getNode('websites')) {
            return $this;
        }
        if (is_numeric($code)) {
            foreach (Mage::getConfig()->getNode('websites')->children() as $websiteCode => $website) {
                if ((int)$website->system->website->id == $code) {
                    $code = $websiteCode;
                    break;
                }
            }
        } else {
            $website = Mage::getConfig()->getNode('websites/' . $code);
        }
        if (!empty($website)) {
            $this->setCode($code);
            $id = (int)$website->system->website->id;
            $this->setId($id)->setStoreId($id);
        }
        return $this;
    }

    /**
     * Get website config data
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path) {
        if (!isset($this->_configCache[$path])) {

            $config = Mage::getConfig()->getNode('websites/' . $this->getCode() . '/' . $path);
            if (!$config) {
                return false;
            }
            if ($config->hasChildren()) {
                $value = array();
                foreach ($config->children() as $k => $v) {
                    $value[$k] = $v;
                }
            } else {
                $value = (string)$config;
            }
            $this->_configCache[$path] = $value;
        }
        return $this->_configCache[$path];
    }

    /**
     * Load group collection and set internal data
     *
     */
    protected function _loadGroups()
    {
        $this->_groups = array();
        $this->_groupsCount = 0;
        foreach ($this->getGroupCollection() as $group) {
            $this->_groups[$group->getId()] = $group;
            $this->_groupIds[$group->getId()] = $group->getId();
            if ($this->getDefaultGroupId() == $group->getId()) {
                $this->_defaultGroup = $group;
            }
            $this->_groupsCount ++;
        }
    }

    /**
     * Set website groups
     *
     * @param array $groups
     * @return Mage_Core_Model_Website
     */
    public function setGroups($groups)
    {
        $this->_groups = array();
        $this->_groupsCount = 0;
        foreach ($groups as $group) {
            $this->_groups[$group->getId()] = $group;
            $this->_groupIds[$group->getId()] = $group->getId();
            if ($this->getDefaultGroupId() == $group->getId()) {
                $this->_defaultGroup = $group;
            }
            $this->_groupsCount++;
        }
        return $this;
    }

    /**
     * Retrieve new (not loaded) Group collection object with website filter
     *
     * @return Mage_Core_Model_Resource_Store_Group_Collection
     */
    public function getGroupCollection()
    {
        return Mage::getModel('Mage_Core_Model_Store_Group')
            ->getCollection()
            ->addWebsiteFilter($this->getId());
    }

    /**
     * Retrieve website groups
     *
     * @return array
     */
    public function getGroups()
    {
        if (is_null($this->_groups)) {
            $this->_loadGroups();
        }
        return $this->_groups;
    }

    /**
     * Retrieve website group ids
     *
     * @return array
     */
    public function getGroupIds()
    {
        if (is_null($this->_groups)) {
            $this->_loadGroups();
        }
        return $this->_groupIds;
    }

    /**
     * Retrieve number groups in a website
     *
     * @return int
     */
    public function getGroupsCount()
    {
        if (is_null($this->_groups)) {
            $this->_loadGroups();
        }
        return $this->_groupsCount;
    }

    /**
     * Retrieve default group model
     *
     * @return Mage_Core_Model_Store_Group
     */
    public function getDefaultGroup()
    {
        if (!$this->hasDefaultGroupId()) {
            return false;
        }
        if (is_null($this->_groups)) {
            $this->_loadGroups();
        }
        return $this->_defaultGroup;
    }

    /**
     * Load store collection and set internal data
     *
     */
    protected function _loadStores()
    {
        $this->_stores = array();
        $this->_storesCount = 0;
        foreach ($this->getStoreCollection() as $store) {
            $this->_stores[$store->getId()] = $store;
            $this->_storeIds[$store->getId()] = $store->getId();
            $this->_storeCodes[$store->getId()] = $store->getCode();
            if ($this->getDefaultGroup() && $this->getDefaultGroup()->getDefaultStoreId() == $store->getId()) {
                $this->_defaultStore = $store;
            }
            $this->_storesCount ++;
        }
    }

    /**
     * Set website stores
     *
     * @param array $stores
     */
    public function setStores($stores)
    {
        $this->_stores = array();
        $this->_storesCount = 0;
        foreach ($stores as $store) {
            $this->_stores[$store->getId()] = $store;
            $this->_storeIds[$store->getId()] = $store->getId();
            $this->_storeCodes[$store->getId()] = $store->getCode();
            if ($this->getDefaultGroup() && $this->getDefaultGroup()->getDefaultStoreId() == $store->getId()) {
                $this->_defaultStore = $store;
            }
            $this->_storesCount ++;
        }
    }

    /**
     * Retrieve new (not loaded) Store collection object with website filter
     *
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function getStoreCollection()
    {
        return Mage::getModel('Mage_Core_Model_Store')
            ->getCollection()
            ->addWebsiteFilter($this->getId());
    }

    /**
     * Retrieve wersite store objects
     *
     * @return array
     */
    public function getStores()
    {
        if (is_null($this->_stores)) {
            $this->_loadStores();
        }
        return $this->_stores;
    }

    /**
     * Retrieve website store ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (is_null($this->_stores)) {
            $this->_loadStores();
        }
        return $this->_storeIds;
    }

    /**
     * Retrieve website store codes
     *
     * @return array
     */
    public function getStoreCodes()
    {
        if (is_null($this->_stores)) {
            $this->_loadStores();
        }
        return $this->_storeCodes;
    }

    /**
     * Retrieve number stores in a website
     *
     * @return int
     */
    public function getStoresCount()
    {
        if (is_null($this->_stores)) {
            $this->_loadStores();
        }
        return $this->_storesCount;
    }

    /**
     * is can delete website
     *
     * @return bool
     */
    public function isCanDelete()
    {
        if ($this->_isReadOnly || !$this->getId()) {
            return false;
        }
        if (is_null($this->_isCanDelete)) {
            $this->_isCanDelete = (Mage::getModel('Mage_Core_Model_Website')->getCollection()->getSize() > 2)
                && !$this->getIsDefault();
        }
        return $this->_isCanDelete;
    }

    /**
     * Retrieve unique website-group-store key for collection with groups and stores
     *
     * @return string
     */
    public function getWebsiteGroupStore()
    {
        return join('-', array($this->getWebsiteId(), $this->getGroupId(), $this->getStoreId()));
    }

    public function getDefaultGroupId()
    {
        return $this->_getData('default_group_id');
    }

    public function getCode()
    {
        return $this->_getData('code');
    }

    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    /**
     * rewrite in order to clear configuration cache
     *
     * @return Mage_Core_Model_Website
     */
    protected function _afterDelete()
    {
        Mage::app()->clearWebsiteCache($this->getId());

        parent::_afterDelete();
        Mage::getConfig()->removeCache();
        return $this;
    }

    /**
     * Retrieve website base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        if ($this->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE)
            == Mage_Core_Model_Store::PRICE_SCOPE_GLOBAL
        ) {
            return Mage::app()->getBaseCurrencyCode();
        } else {
            return $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        }
    }

    /**
     * Retrieve website base currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('Mage_Directory_Model_Currency')->load($this->getBaseCurrencyCode());
            $this->setData('base_currency', $currency);
        }
        return $currency;
    }

    /**
     * Retrieve Default Website Store or null
     *
     * @return Mage_Core_Model_Store
     */
    public function getDefaultStore()
    {
        // init stores if not loaded
        $this->getStores();
        return $this->_defaultStore;
    }

    /**
     * Retrieve default stores select object
     * Select fields website_id, store_id
     *
     * @param bool $withDefault include/exclude default admin website
     * @return Varien_Db_Select
     */
    public function getDefaultStoresSelect($withDefault = false)
    {
        return $this->getResource()->getDefaultStoresSelect($withDefault);
    }

    /**
     * Get/Set isReadOnly flag
     *
     * @param bool $value
     * @return bool
     */
    public function isReadOnly($value = null)
    {
        if (null !== $value) {
            $this->_isReadOnly = (bool)$value;
        }
        return $this->_isReadOnly;
    }
}
