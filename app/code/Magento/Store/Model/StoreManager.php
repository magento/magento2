<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ResourceModel\StoreWebsiteRelation;

/**
 * Service contract, which manage scopes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreManager implements
    \Magento\Store\Model\StoreManagerInterface,
    \Magento\Store\Api\StoreWebsiteRelationInterface
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
     * Wether single store mode enabled or not
     */
    const XML_PATH_SINGLE_STORE_MODE_ENABLED = 'general/single_store_mode/enabled';

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreResolverInterface
     */
    protected $storeResolver;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $cache;

    /**
     * Default store code
     *
     * @var string
     */
    protected $currentStoreId = null;

    /**
     * Flag that shows that system has only one store view
     *
     * @var bool
     */
    protected $_hasSingleStore;

    /**
     * Flag is single store mode allowed
     *
     * @var bool
     */
    protected $isSingleStoreAllowed;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param StoreResolverInterface $storeResolver
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param bool $isSingleStoreAllowed
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreResolverInterface $storeResolver,
        \Magento\Framework\Cache\FrontendInterface $cache,
        $isSingleStoreAllowed = true
    ) {
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeResolver = $storeResolver;
        $this->cache = $cache;
        $this->isSingleStoreAllowed = $isSingleStoreAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentStore($store)
    {
        $this->currentStoreId = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->isSingleStoreAllowed = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSingleStore()
    {
        // TODO: MAGETWO-39902 add cache, move value to consts
        return $this->isSingleStoreAllowed && count($this->getStores(true)) < 3;
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleStoreMode()
    {
        return $this->isSingleStoreModeEnabled() && $this->hasSingleStore();
    }

    /**
     * {@inheritdoc}
     */
    public function getStore($storeId = null)
    {
        if (!isset($storeId) || '' === $storeId || $storeId === true) {
            if (null === $this->currentStoreId) {
                \Magento\Framework\Profiler::start('store.resolve');
                $this->currentStoreId = $this->storeResolver->getCurrentStoreId();
                \Magento\Framework\Profiler::stop('store.resolve');
            }
            $storeId = $this->currentStoreId;
        }
        if ($storeId instanceof \Magento\Store\Api\Data\StoreInterface) {
            return $storeId;
        }

        $store = is_numeric($storeId)
            ? $this->storeRepository->getById($storeId)
            : $this->storeRepository->get($storeId);

        return $store;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function reinitStores()
    {
        $this->currentStoreId = null;
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [StoreResolver::CACHE_TAG, Store::CACHE_TAG]);
        $this->scopeConfig->clean();
        $this->storeRepository->clean();
        $this->websiteRepository->clean();
        $this->groupRepository->clean();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultStoreView()
    {
        $defaultWebsite = $this->websiteRepository->getDefault();
        $defaultStore = $this->getGroup($defaultWebsite->getDefaultGroupId())->getDefaultStore();
        return $defaultStore ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup($groupId = null)
    {
        if (null === $groupId) {
            $group = $this->groupRepository->get($this->getStore()->getGroupId());
        } elseif ($groupId instanceof \Magento\Store\Api\Data\GroupInterface) {
            $group = $groupId;
        } else {
            $group = $this->groupRepository->get($groupId);
        }
        return $group;
    }

    /**
     * {@inheritdoc}
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
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SINGLE_STORE_MODE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @deprecated
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
