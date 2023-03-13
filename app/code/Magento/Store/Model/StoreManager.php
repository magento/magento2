<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Profiler;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ResourceModel\StoreWebsiteRelation;
use Zend_Cache;

/**
 * Service contract, which manage scopes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreManager implements StoreManagerInterface, StoreWebsiteRelationInterface
{
    /**
     * Application run code
     */
    const PARAM_RUN_CODE = 'MAGE_RUN_CODE';

    /**
     * Application run type (store|website)
     */
    const PARAM_RUN_TYPE = 'MAGE_RUN_TYPE';

    /**
     * Whether single store mode enabled or not
     */
    const XML_PATH_SINGLE_STORE_MODE_ENABLED = 'general/single_store_mode/enabled';

    /**
     * Default store code
     *
     * @var string|int|StoreInterface
     */
    protected $currentStoreId = null;

    /**
     * Flag that shows that system has only one store view
     *
     * @var bool
     */
    protected $_hasSingleStore;

    /**
     * StoreManager constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ScopeConfigInterface $scopeConfig Scope config
     * @param StoreResolverInterface $storeResolver
     * @param FrontendInterface $cache
     * @param bool $isSingleStoreAllowed Flag is single store mode allowed
     */
    public function __construct(
        protected readonly StoreRepositoryInterface $storeRepository,
        protected readonly GroupRepositoryInterface $groupRepository,
        protected readonly WebsiteRepositoryInterface $websiteRepository,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly StoreResolverInterface $storeResolver,
        protected readonly FrontendInterface $cache,
        protected $isSingleStoreAllowed = true
    ) {
    }

    /**
     * @inheritdoc
     */
    public function setCurrentStore($store)
    {
        $this->currentStoreId = $store;
    }

    /**
     * @inheritdoc
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->isSingleStoreAllowed = $value;
    }

    /**
     * @inheritdoc
     */
    public function hasSingleStore()
    {
        // TODO: MAGETWO-39902 add cache, move value to consts
        return $this->isSingleStoreAllowed && count($this->getStores(true)) < 3;
    }

    /**
     * @inheritdoc
     */
    public function isSingleStoreMode()
    {
        return $this->isSingleStoreModeEnabled() && $this->hasSingleStore();
    }

    /**
     * @inheritdoc
     */
    public function getStore($storeId = null)
    {
        if (!isset($storeId) || '' === $storeId || $storeId === true) {
            if (null === $this->currentStoreId) {
                Profiler::start('store.resolve');
                $this->currentStoreId = $this->storeResolver->getCurrentStoreId();
                Profiler::stop('store.resolve');
            }
            $storeId = $this->currentStoreId;
        }
        if ($storeId instanceof StoreInterface) {
            return $storeId;
        }

        $store = is_numeric($storeId)
            ? $this->storeRepository->getById($storeId)
            : $this->storeRepository->get($storeId);

        return $store;
    }

    /**
     * @inheritdoc
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        $stores = [];
        foreach ($this->storeRepository->getList() as $store) {
            if (!$withDefault && $store->getId() == 0) {
                continue;
            }
            if ($codeKey) {
                $stores[$store->getCode()] = $store;
            } else {
                $stores[$store->getId()] = $store;
            }
        }
        return $stores;
    }

    /**
     * @inheritdoc
     */
    public function getWebsite($websiteId = null)
    {
        if ($websiteId === null || $websiteId === '') {
            $website = $this->websiteRepository->getById($this->getStore()->getWebsiteId());
        } elseif ($websiteId instanceof Website) {
            $website = $websiteId;
        } elseif ($websiteId === true) {
            $website = $this->websiteRepository->getDefault();
        } elseif (is_numeric($websiteId)) {
            $website = $this->websiteRepository->getById($websiteId);
        } else {
            $website = $this->websiteRepository->get($websiteId);
        }

        return $website;
    }

    /**
     * @inheritdoc
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = [];
        foreach ($this->websiteRepository->getList() as $website) {
            if (!$withDefault && $website->getId() == 0) {
                continue;
            }
            if ($codeKey) {
                $websites[$website->getCode()] = $website;
            } else {
                $websites[$website->getId()] = $website;
            }
        }
        return $websites;
    }

    /**
     * @inheritdoc
     */
    public function reinitStores()
    {
        $this->currentStoreId = null;
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [StoreResolver::CACHE_TAG, Store::CACHE_TAG]);
        $this->scopeConfig->clean();
        $this->storeRepository->clean();
        $this->websiteRepository->clean();
        $this->groupRepository->clean();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultStoreView()
    {
        $defaultWebsite = $this->websiteRepository->getDefault();
        $defaultStore = $this->getGroup($defaultWebsite->getDefaultGroupId())->getDefaultStore();
        return $defaultStore ?: null;
    }

    /**
     * @inheritdoc
     */
    public function getGroup($groupId = null)
    {
        if (null === $groupId) {
            $group = $this->groupRepository->get($this->getStore()->getGroupId());
        } elseif ($groupId instanceof GroupInterface) {
            $group = $groupId;
        } else {
            $group = $this->groupRepository->get($groupId);
        }
        return $group;
    }

    /**
     * @inheritdoc
     */
    public function getGroups($withDefault = false)
    {
        $groups = $this->groupRepository->getList();

        return $withDefault ? $groups : array_filter(
            $groups,
            function ($item) {
                return $item->getId() != 0;
            }
        );
    }

    /**
     * Check if Single-Store mode is enabled in configuration
     *
     * This flag only shows that admin does not want to show certain UI components at backend (like store switchers etc)
     * if Magento has only one store view but it does not check the store view collection
     *
     * @return bool
     */
    protected function isSingleStoreModeEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SINGLE_STORE_MODE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Store Website Relation
     *
     * @deprecated 100.2.0
     * @return StoreWebsiteRelation
     */
    private function getStoreWebsiteRelation()
    {
        return ObjectManager::getInstance()->get(StoreWebsiteRelation::class);
    }

    /**
     * @inheritdoc
     */
    public function getStoreByWebsiteId($websiteId)
    {
        return $this->getStoreWebsiteRelation()->getStoreByWebsiteId($websiteId);
    }
}
