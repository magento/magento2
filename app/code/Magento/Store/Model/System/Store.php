<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Store\Model\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Core System Store Model
 *
 * @api
 * @since 100.0.2
 */
class Store extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    /**
     * Website collection
     * websiteId => \Magento\Store\Model\Website
     *
     * @var array
     */
    protected $_websiteCollection = [];

    /**
     * Group collection
     * groupId => \Magento\Store\Model\Group
     *
     * @var array
     */
    protected $_groupCollection = [];

    /**
     * Store collection
     * storeId => \Magento\Store\Model\Store
     *
     * @var array
     */
    protected $_storeCollection;

    /**
     * @var bool
     */
    private $_isAdminScopeAllowed = true;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Init model
     * Load Website, Group and Store collections
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
        return $this->reload();
    }

    /**
     * Load/Reload Website collection
     *
     * @return $this
     */
    protected function _loadWebsiteCollection()
    {
        $this->_websiteCollection = $this->_storeManager->getWebsites();
        return $this;
    }

    /**
     * Load/Reload Group collection
     *
     * @return $this
     */
    protected function _loadGroupCollection()
    {
        $this->_groupCollection = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $this->_groupCollection[$group->getId()] = $group;
            }
        }
        return $this;
    }

    /**
     * Load/Reload Store collection
     *
     * @return $this
     */
    protected function _loadStoreCollection()
    {
        $this->_storeCollection = $this->_storeManager->getStores();
        return $this;
    }

    /**
     * Retrieve store values for form
     *
     * @param bool $empty
     * @param bool $all
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getStoreValuesForForm($empty = false, $all = false)
    {
        $options = [];
        if ($empty) {
            $options[] = ['label' => '', 'value' => ''];
        }
        if ($all && $this->_isAdminScopeAllowed) {
            $options[] = ['label' => __('All Store Views'), 'value' => 0];
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');

        foreach ($this->_websiteCollection as $website) {
            $websiteShow = false;
            foreach ($this->_groupCollection as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                $values = [];
                foreach ($this->_storeCollection as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $options[] = ['label' => $website->getName(), 'value' => []];
                        $websiteShow = true;
                    }
                    $values[] = [
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $store->getName(),
                        'value' => $store->getId(),
                        '__disableTmpl' => true,
                    ];
                }
                if (!empty($values)) {
                    $options[] = [
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $group->getName(),
                        'value' => $values,
                    ];
                }
            }
        }
        array_walk(
            $options,
            function (&$item) {
                $item['__disableTmpl'] = true;
            }
        );
        return $options;
    }

    /**
     * Retrieve stores structure
     *
     * @param bool $isAll
     * @param array $storeIds
     * @param array $groupIds
     * @param array $websiteIds
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getStoresStructure($isAll = false, $storeIds = [], $groupIds = [], $websiteIds = [])
    {
        $out = [];
        $websites = $this->getWebsiteCollection();

        if ($isAll) {
            $out[] = ['value' => 0, 'label' => __('All Store Views')];
        }

        foreach ($websites as $website) {
            $websiteId = $website->getId();
            if ($websiteIds && !in_array($websiteId, $websiteIds)) {
                continue;
            }
            $out[$websiteId] = ['value' => $websiteId, 'label' => $website->getName()];

            foreach ($website->getGroups() as $group) {
                $groupId = $group->getId();
                if ($groupIds && !in_array($groupId, $groupIds)) {
                    continue;
                }
                $out[$websiteId]['children'][$groupId] = ['value' => $groupId, 'label' => $group->getName()];

                foreach ($group->getStores() as $store) {
                    $storeId = $store->getId();
                    if ($storeIds && !in_array($storeId, $storeIds)) {
                        continue;
                    }
                    $out[$websiteId]['children'][$groupId]['children'][$storeId] = [
                        'value' => $storeId,
                        'label' => $store->getName(),
                    ];
                }
                if (empty($out[$websiteId]['children'][$groupId]['children'])) {
                    unset($out[$websiteId]['children'][$groupId]);
                }
            }
            if (empty($out[$websiteId]['children'])) {
                unset($out[$websiteId]);
            }
        }
        return $out;
    }

    /**
     * Website label/value array getter, compatible with form dropdown options
     *
     * @param bool $empty
     * @param bool $all
     * @return array
     */
    public function getWebsiteValuesForForm($empty = false, $all = false)
    {
        $options = [];
        if ($empty) {
            $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        }
        if ($all && $this->_isAdminScopeAllowed) {
            $options[] = ['label' => __('Admin'), 'value' => 0];
        }

        foreach ($this->_websiteCollection as $website) {
            $options[] = ['label' => $website->getName(), 'value' => $website->getId()];
        }
        return $options;
    }

    /**
     * Get websites as id => name associative array
     *
     * @param bool $withDefault
     * @param string $attribute
     * @return array
     */
    public function getWebsiteOptionHash($withDefault = false, $attribute = 'name')
    {
        $options = [];
        foreach ($this->_storeManager->getWebsites((bool)$withDefault && $this->_isAdminScopeAllowed) as $website) {
            $options[$website->getId()] = $website->getDataUsingMethod($attribute);
        }
        return $options;
    }

    /**
     * Get store views as id => name associative array
     *
     * @param bool $withDefault
     * @param string $attribute
     * @return array
     */
    public function getStoreOptionHash($withDefault = false, $attribute = 'name')
    {
        $options = [];
        foreach ($this->_storeManager->getStores((bool)$withDefault && $this->_isAdminScopeAllowed) as $store) {
            $options[$store->getId()] = $store->getDataUsingMethod($attribute);
        }
        return $options;
    }

    /**
     * Get store groups as id => name associative array
     *
     * @param string $attribute
     * @return array
     */
    public function getStoreGroupOptionHash($attribute = 'name')
    {
        $options = [];
        foreach ($this->_groupCollection as $group) {
            $options[$group->getId()] = $group->getDataUsingMethod($attribute);
        }
        return $options;
    }

    /**
     * Retrieve Website name by Id
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getWebsiteName($websiteId)
    {
        foreach ($this->_websiteCollection as $website) {
            if ($website->getId() == $websiteId) {
                return $website->getName();
            }
        }
        return null;
    }

    /**
     * Retrieve Group name by Id
     *
     * @param int $groupId
     * @return string|null
     */
    public function getGroupName($groupId)
    {
        foreach ($this->_groupCollection as $group) {
            if ($group->getId() == $groupId) {
                return $group->getName();
            }
        }
        return null;
    }

    /**
     * Retrieve Store name by Id
     *
     * @param int $storeId
     * @return string|null
     */
    public function getStoreName($storeId)
    {
        if (isset($this->_storeCollection[$storeId])) {
            return $this->_storeCollection[$storeId]->getName();
        }
        return null;
    }

    /**
     * Retrieve store name with website and website store
     *
     * @param  int $storeId
     * @return \Magento\Store\Model\Store|null
     */
    public function getStoreData($storeId)
    {
        if (isset($this->_storeCollection[$storeId])) {
            return $this->_storeCollection[$storeId];
        }
        return null;
    }

    /**
     * Retrieve store name with website and website store
     *
     * @param  int $storeId
     * @return string
     */
    public function getStoreNameWithWebsite($storeId)
    {
        $name = '';
        if (is_array($storeId)) {
            $names = [];
            foreach ($storeId as $id) {
                $names[] = $this->getStoreNameWithWebsite($id);
            }
            $name = implode(', ', $names);
        } else {
            if (isset($this->_storeCollection[$storeId])) {
                $data = $this->_storeCollection[$storeId];
                $name .= $this->getWebsiteName($data->getWebsiteId());
                $name .= ($name ? '/' : '') . $this->getGroupName($data->getGroupId());
                $name .= ($name ? '/' : '') . $data->getName();
            }
        }
        return $name;
    }

    /**
     * Retrieve Website collection as array
     *
     * @return array
     */
    public function getWebsiteCollection()
    {
        return $this->_websiteCollection;
    }

    /**
     * Retrieve Group collection as array
     *
     * @return array
     */
    public function getGroupCollection()
    {
        return $this->_groupCollection;
    }

    /**
     * Retrieve Store collection as array
     *
     * @return array
     */
    public function getStoreCollection()
    {
        return $this->_storeCollection;
    }

    /**
     * Load/Reload collection(s) by type
     *
     * Allowed types: website, group, store or null for all
     *
     * @param string $type
     * @return $this
     */
    public function reload($type = null)
    {
        if ($type === null) {
            $this->_loadWebsiteCollection();
            $this->_loadGroupCollection();
            $this->_loadStoreCollection();
        } else {
            switch ($type) {
                case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE:
                    $this->_loadWebsiteCollection();
                    break;
                case \Magento\Store\Model\ScopeInterface::SCOPE_GROUP:
                    $this->_loadGroupCollection();
                    break;
                case \Magento\Store\Model\ScopeInterface::SCOPE_STORE:
                    $this->_loadStoreCollection();
                    break;
                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * Retrieve store path with website and website store
     *
     * @param  int $storeId
     * @return string
     */
    public function getStoreNamePath($storeId)
    {
        $name = '';
        if (is_array($storeId)) {
            $names = [];
            foreach ($storeId as $id) {
                $names[] = $this->getStoreNamePath($id);
            }
            $name = implode(', ', $names);
        } else {
            if (isset($this->_storeCollection[$storeId])) {
                $data = $this->_storeCollection[$storeId];
                $name .= $this->getWebsiteName($data->getWebsiteId());
                $name .= ($name ? '/' : '') . $this->getGroupName($data->getGroupId());
            }
        }
        return $name;
    }

    /**
     * Specify whether to show admin-scope options
     *
     * @param bool $value
     * @return $this
     */
    public function setIsAdminScopeAllowed($value)
    {
        $this->_isAdminScopeAllowed = (bool)$value;
        return $this;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->getStoreValuesForForm();
    }
}
