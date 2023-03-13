<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store and language switcher block
 */
namespace Magento\Store\Block;

use Magento\Directory\Helper\Data;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Store\Model\Store as ModelStore;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Switcher block
 *
 * @api
 * @since 100.0.2
 */
class Switcher extends Template
{
    /**
     * @var bool
     */
    protected $_storeInUrl;

    /**
     * @var PostHelper
     */
    protected $_postDataHelper;

    /**
     * @param TemplateContext $context
     * @param PostHelper $postDataHelper
     * @param array $data
     * @param null|UrlHelper $urlHelper
     */
    public function __construct(
        TemplateContext $context,
        PostHelper $postDataHelper,
        array $data = [],
        private ?UrlHelper $urlHelper = null
    ) {
        $this->_postDataHelper = $postDataHelper;
        parent::__construct($context, $data);
        $this->urlHelper = $urlHelper ?: ObjectManager::getInstance()->get(UrlHelper::class);
    }

    /**
     * Get current website Id.
     *
     * @return int|null|string
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get current group Id.
     *
     * @return int|null|string
     */
    public function getCurrentGroupId()
    {
        return $this->_storeManager->getStore()->getGroupId();
    }

    /**
     * Get current Store Id.
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get raw groups.
     *
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
     * Get raw stores.
     *
     * @return array
     */
    public function getRawStores()
    {
        if (!$this->hasData('raw_stores')) {
            $websiteStores = $this->_storeManager->getWebsite()->getStores();
            $stores = [];
            foreach ($websiteStores as $store) {
                /* @var $store ModelStore */
                if (!$store->isActive()) {
                    continue;
                }
                $localeCode = $this->_scopeConfig->getValue(
                    Data::XML_PATH_DEFAULT_LOCALE,
                    ScopeInterface::SCOPE_STORE,
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
                Data::XML_PATH_DEFAULT_LOCALE,
                ScopeInterface::SCOPE_STORE
            );
            foreach ($rawGroups as $group) {
                /* @var Group $group */
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
                    $group->setSortOrder($store->getSortOrder());
                    $groups[] = $group;
                }
            }

            usort($groups, static function ($itemA, $itemB) {
                return (int)$itemA->getSortOrder() <=> (int)$itemB->getSortOrder();
            });

            $this->setData('groups', $groups);
        }
        return $this->getData('groups');
    }

    /**
     * Get stores.
     *
     * @return ModelStore[]
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

                uasort($stores, static function ($itemA, $itemB) {
                    return (int)$itemA->getSortOrder() <=> (int)$itemB->getSortOrder();
                });
            }

            $this->setData('stores', $stores);
        }
        return $this->getData('stores');
    }

    /**
     * Get current store code.
     *
     * @return string
     */
    public function getCurrentStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * Is store in url.
     *
     * @return bool
     */
    public function isStoreInUrl()
    {
        if ($this->_storeInUrl === null) {
            $this->_storeInUrl = $this->_storeManager->getStore()->isUseStoreInUrl();
        }
        return $this->_storeInUrl;
    }

    /**
     * Get store code.
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * Get store name.
     *
     * @return null|string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    /**
     * Returns target store post data.
     *
     * @param Store $store
     * @param array $data
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTargetStorePostData(Store $store, $data = [])
    {
        $data[StoreManagerInterface::PARAM_NAME] = $store->getCode();
        $data['___from_store'] = $this->_storeManager->getStore()->getCode();

        $urlOnTargetStore = $store->getCurrentUrl(false);
        $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->urlHelper->getEncodedUrl($urlOnTargetStore);

        $url = $this->getUrl('stores/store/redirect');

        return $this->_postDataHelper->getPostData(
            $url,
            $data
        );
    }
}
