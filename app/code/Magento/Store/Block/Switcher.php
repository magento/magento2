<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store and language switcher block
 */
namespace Magento\Store\Block;

use Magento\Store\Model\Group;

class Switcher extends \Magento\Framework\View\Element\Template
{
    /**
     * @var bool
     */
    protected $_storeInUrl;

    /**
     * @var \Magento\Core\Helper\PostData
     */
    protected $_postDataHelper;

    /**
     * Constructs
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\PostData $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Core\Helper\PostData $postDataHelper,
        array $data = []
    ) {
        $this->_postDataHelper = $postDataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return int|null|string
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return int|null|string
     */
    public function getCurrentGroupId()
    {
        return $this->_storeManager->getStore()->getGroupId();
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return array
     */
    public function getRawGroups()
    {
        if (!$this->hasData('raw_groups')) {
            $websiteGroups = $this->_storeManager->getWebsite()->getGroups();

            $groups = [];
            foreach ($websiteGroups as $group) {
                $groups[$group->getId()] = $group;
            }
            $this->setData('raw_groups', $groups);
        }
        return $this->getData('raw_groups');
    }

    /**
     * @return array
     */
    public function getRawStores()
    {
        if (!$this->hasData('raw_stores')) {
            $websiteStores = $this->_storeManager->getWebsite()->getStores();
            $stores = [];
            foreach ($websiteStores as $store) {
                /* @var $store \Magento\Store\Model\Store */
                if (!$store->getIsActive()) {
                    continue;
                }
                $localeCode = $this->_scopeConfig->getValue(
                    \Magento\Core\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                );
                $store->setLocaleCode($localeCode);
                $params = ['_query' => []];
                if (!$this->isStoreInUrl()) {
                    $params['_query']['___store'] = $store->getCode();
                }
                $baseUrl = $store->getUrl('', $params);

                $store->setHomeUrl($baseUrl);
                $stores[$store->getGroupId()][$store->getId()] = $store;
            }
            $this->setData('raw_stores', $stores);
        }
        return $this->getData('raw_stores');
    }

    /**
     * Retrieve list of store groups with default urls set
     *
     * @return Group[]
     */
    public function getGroups()
    {
        if (!$this->hasData('groups')) {
            $rawGroups = $this->getRawGroups();
            $rawStores = $this->getRawStores();

            $groups = [];
            $localeCode = $this->_scopeConfig->getValue(
                \Magento\Core\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            foreach ($rawGroups as $group) {
                /* @var $group Group */
                if (!isset($rawStores[$group->getId()])) {
                    continue;
                }
                if ($group->getId() == $this->getCurrentGroupId()) {
                    $groups[] = $group;
                    continue;
                }

                $store = $group->getDefaultStoreByLocale($localeCode);

                if ($store) {
                    $group->setHomeUrl($store->getHomeUrl());
                    $groups[] = $group;
                }
            }
            $this->setData('groups', $groups);
        }
        return $this->getData('groups');
    }

    /**
     * @return \Magento\Store\Model\Store[]
     */
    public function getStores()
    {
        if (!$this->getData('stores')) {
            $rawStores = $this->getRawStores();

            $groupId = $this->getCurrentGroupId();
            if (!isset($rawStores[$groupId])) {
                $stores = [];
            } else {
                $stores = $rawStores[$groupId];
            }
            $this->setData('stores', $stores);
        }
        return $this->getData('stores');
    }

    /**
     * @return string
     */
    public function getCurrentStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return bool
     */
    public function isStoreInUrl()
    {
        if (is_null($this->_storeInUrl)) {
            $this->_storeInUrl = $this->_storeManager->getStore()->isUseStoreInUrl();
        }
        return $this->_storeInUrl;
    }

    /**
     * Get store code
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * Get store name
     *
     * @return null|string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    /**
     * Returns target store post data
     *
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function getTargetStorePostData(\Magento\Store\Model\Store $store)
    {
        return $this->_postDataHelper->getPostData(
            $this->getHomeUrl(),
            ['___store' => $store->getCode(), '___from_store' => $this->getStoreCode()]
        );
    }
}
