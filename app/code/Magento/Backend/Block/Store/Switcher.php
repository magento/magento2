<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Store;

/**
 * Store switcher block
 */
class Switcher extends \Magento\Backend\Block\Template
{
    /**
     * URL for store switcher hint
     */
    const HINT_URL = 'http://docs.magento.com/m2/ce/user_guide/configuration/scope.html';

    /**
     * Name of website variable
     *
     * @var string
     */
    protected $_defaultWebsiteVarName = 'website';

    /**
     * Name of store group variable
     *
     * @var string
     */
    protected $_defaultStoreGroupVarName = 'group';

    /**
     * Name of store variable
     *
     * @var string
     */
    protected $_defaultStoreVarName = 'store';

    /**
     * @var array
     */
    protected $_storeIds;

    /**
     * Url for store switcher hint
     *
     * @var string
     */
    protected $_hintUrl;

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::store/switcher.phtml';

    /**
     * Website factory
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Store Group Factory
     *
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_storeGroupFactory;

    /**
     * Store Factory
     *
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_websiteFactory = $websiteFactory;
        $this->_storeGroupFactory = $storeGroupFactory;
        $this->_storeFactory = $storeFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setUseConfirm(true);
        $this->setUseAjax(true);

        $this->setShowManageStoresLink(0);

        if (!$this->hasData('switch_websites')) {
            $this->setSwitchWebsites(false);
        }
        if (!$this->hasData('switch_store_groups')) {
            $this->setSwitchStoreGroups(false);
        }
        if (!$this->hasData('switch_store_views')) {
            $this->setSwitchStoreViews(true);
        }
        $this->setDefaultSelectionName(__('All Store Views'));
    }

    /**
     * @return \Magento\Store\Model\ResourceModel\Website\Collection
     */
    public function getWebsiteCollection()
    {
        $collection = $this->_websiteFactory->create()->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if ($websiteIds !== null) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    /**
     * Get websites
     *
     * @return \Magento\Store\Model\Website[]
     */
    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            $websites = array_intersect_key($websites, array_flip($websiteIds));
        }
        return $websites;
    }

    /**
     * Check if can switch to websites
     *
     * @return bool
     */
    public function isWebsiteSwitchEnabled()
    {
        return (bool)$this->getData('switch_websites');
    }

    /**
     * @param string $varName
     * @return $this
     */
    public function setWebsiteVarName($varName)
    {
        $this->setData('website_var_name', $varName);
        return $this;
    }

    /**
     * @return string
     */
    public function getWebsiteVarName()
    {
        if ($this->hasData('website_var_name')) {
            return (string)$this->getData('website_var_name');
        } else {
            return (string)$this->_defaultWebsiteVarName;
        }
    }

    /**
     * @param \Magento\Store\Model\Website $website
     * @return bool
     */
    public function isWebsiteSelected(\Magento\Store\Model\Website $website)
    {
        return $this->getWebsiteId() === $website->getId() && $this->getStoreId() === null;
    }

    /**
     * @return int|null
     */
    public function getWebsiteId()
    {
        if (!$this->hasData('website_id')) {
            $this->setData('website_id', (int)$this->getRequest()->getParam($this->getWebsiteVarName()));
        }
        return $this->getData('website_id');
    }

    /**
     * @param int|\Magento\Store\Model\Website $website
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroupCollection($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_websiteFactory->create()->load($website);
        }
        return $website->getGroupCollection();
    }

    /**
     * Get store groups for specified website
     *
     * @param \Magento\Store\Model\Website|int $website
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_storeManager->getWebsite($website);
        }
        return $website->getGroups();
    }

    /**
     * Check if can switch to store group
     *
     * @return bool
     */
    public function isStoreGroupSwitchEnabled()
    {
        return (bool)$this->getData('switch_store_groups');
    }

    /**
     * @param string $varName
     * @return $this
     */
    public function setStoreGroupVarName($varName)
    {
        $this->setData('store_group_var_name', $varName);
        return $this;
    }

    /**
     * @return string
     */
    public function getStoreGroupVarName()
    {
        if ($this->hasData('store_group_var_name')) {
            return (string)$this->getData('store_group_var_name');
        } else {
            return (string)$this->_defaultStoreGroupVarName;
        }
    }

    /**
     * @param \Magento\Store\Model\Group $group
     * @return bool
     */
    public function isStoreGroupSelected(\Magento\Store\Model\Group $group)
    {
        return $this->getStoreGroupId() === $group->getId() && $this->getStoreGroupId() === null;
    }

    /**
     * @return int|null
     */
    public function getStoreGroupId()
    {
        if (!$this->hasData('store_group_id')) {
            $this->setData('store_group_id', (int)$this->getRequest()->getParam($this->getStoreGroupVarName()));
        }
        return $this->getData('store_group_id');
    }

    /**
     * @param \Magento\Store\Model\Group|int $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_storeGroupFactory->create()->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    /**
     * Get store views for specified store group
     *
     * @param \Magento\Store\Model\Group|int $group
     * @return \Magento\Store\Model\Store[]
     */
    public function getStores($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_storeManager->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach (array_keys($stores) as $storeId) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        if (!$this->hasData('store_id')) {
            $this->setData('store_id', (int)$this->getRequest()->getParam($this->getStoreVarName()));
        }
        return $this->getData('store_id');
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function isStoreSelected(\Magento\Store\Model\Store $store)
    {
        return $this->getStoreId() !== null && (int)$this->getStoreId() === (int)$store->getId();
    }

    /**
     * Check if can switch to store views
     *
     * @return bool
     */
    public function isStoreSwitchEnabled()
    {
        return (bool)$this->getData('switch_store_views');
    }

    /**
     * @param string $varName
     * @return $this
     */
    public function setStoreVarName($varName)
    {
        $this->setData('store_var_name', $varName);
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getStoreVarName()
    {
        if ($this->hasData('store_var_name')) {
            return (string)$this->getData('store_var_name');
        } else {
            return (string)$this->_defaultStoreVarName;
        }
    }

    /**
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl(
            '*/*/*',
            [
                '_current' => true,
                $this->getStoreVarName() => null,
                $this->getStoreGroupVarName() => null,
                $this->getWebsiteVarName() => null,
            ]
        );
    }

    /**
     * @return bool
     */
    public function hasScopeSelected()
    {
        return $this->getStoreId() !== null || $this->getStoreGroupId() !== null || $this->getWebsiteId() !== null;
    }

    /**
     * Get current selection name
     *
     * @return string
     */
    public function getCurrentSelectionName()
    {
        if (!($name = $this->getCurrentStoreName())) {
            if (!($name = $this->getCurrentStoreGroupName())) {
                if (!($name = $this->getCurrentWebsiteName())) {
                    $name = $this->getDefaultSelectionName();
                }
            }
        }
        return $name;
    }

    /**
     * Get current website name
     *
     * @return string
     */
    public function getCurrentWebsiteName()
    {
        if ($this->getWebsiteId() !== null) {
            $website = $this->_websiteFactory->create();
            $website->load($this->getWebsiteId());
            if ($website->getId()) {
                return $website->getName();
            }
        }
    }

    /**
     * Get current store group name
     *
     * @return string
     */
    public function getCurrentStoreGroupName()
    {
        if ($this->getStoreGroupId() !== null) {
            $group = $this->_storeGroupFactory->create();
            $group->load($this->getStoreGroupId());
            if ($group->getId()) {
                return $group->getName();
            }
        }
    }

    /**
     * Get current store view name
     *
     * @return string
     */
    public function getCurrentStoreName()
    {
        if ($this->getStoreId() !== null) {
            $store = $this->_storeFactory->create();
            $store->load($this->getStoreId());
            if ($store->getId()) {
                return $store->getName();
            }
        }
    }

    /**
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    /**
     * @return bool
     */
    public function isShow()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isShow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }

    /**
     * Return url for store switcher hint
     *
     * @return string
     */
    public function getHintUrl()
    {
        return self::HINT_URL;
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<div class="admin__field-tooltip tooltip">' . '<a' . ' href="' . $this->escapeUrl(
                $url
            ) . '"' . ' onclick="this.target=\'_blank\'"' . ' title="' . __(
                'What is this?'
            ) . '"' . ' class="admin__field-tooltip-action action-help"><span>' . __(
                'What is this?'
            ) . '</span></a></span>' . ' </div>';
        }
        return $html;
    }

    /**
     * Get whether iframe is being used
     *
     * @return bool
     */
    public function isUsingIframe()
    {
        if ($this->hasData('is_using_iframe')) {
            return (bool)$this->getData('is_using_iframe');
        }
        return false;
    }
}
