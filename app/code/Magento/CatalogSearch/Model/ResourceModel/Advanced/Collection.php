<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Advanced;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\CatalogSearch\Model\ResourceModel\Advanced;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\DefaultFilterStrategyApplyChecker;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\DefaultFilterStrategyApplyCheckerInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\Search\Request\EmptyRequestDataException;
use Magento\Framework\Search\Request\NonExistingRequestNameException;

/**
 * Advanced search collection
 *
 * This collection should be refactored to not have dependencies on MySQL-specific implementation.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * List Of filters
     * @var array
     */
    private $filters = [];

    /**
     * @var \Magento\Search\Api\SearchInterface
     */
    private $search;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchResultInterface
     */
    private $searchResult;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var SearchCriteriaResolverFactory
     */
    private $searchCriteriaResolverFactory;

    /**
     * @var SearchResultApplierFactory
     */
    private $searchResultApplierFactory;

    /**
     * @var TotalRecordsResolverFactory
     */
    private $totalRecordsResolverFactory;

    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @var array
     */
    private $searchOrders;

    /**
     * @var DefaultFilterStrategyApplyCheckerInterface
     */
    private $defaultFilterStrategyApplyChecker;

    /**
     * @var Advanced
     */
    private $advancedSearchResource;

    /**
     * Collection constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param mixed $requestBuilder
     * @param mixed $searchEngine
     * @param mixed $temporaryStorageFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param SearchResultFactory|null $searchResultFactory
     * @param ProductLimitationFactory|null $productLimitationFactory
     * @param MetadataPool|null $metadataPool
     * @param string $searchRequestName
     * @param SearchCriteriaResolverFactory|null $searchCriteriaResolverFactory
     * @param SearchResultApplierFactory|null $searchResultApplierFactory
     * @param TotalRecordsResolverFactory|null $totalRecordsResolverFactory
     * @param EngineResolverInterface|null $engineResolver
     * @param DefaultFilterStrategyApplyCheckerInterface|null $defaultFilterStrategyApplyChecker
     * @param Advanced|null $advancedSearchResource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        $requestBuilder = null,
        $searchEngine = null,
        $temporaryStorageFactory = null,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?SearchResultFactory $searchResultFactory = null,
        ?ProductLimitationFactory $productLimitationFactory = null,
        ?MetadataPool $metadataPool = null,
        $searchRequestName = 'advanced_search_container',
        ?SearchCriteriaResolverFactory $searchCriteriaResolverFactory = null,
        ?SearchResultApplierFactory $searchResultApplierFactory = null,
        ?TotalRecordsResolverFactory $totalRecordsResolverFactory = null,
        ?EngineResolverInterface $engineResolver = null,
        ?DefaultFilterStrategyApplyCheckerInterface $defaultFilterStrategyApplyChecker = null,
        ?Advanced $advancedSearchResource = null
    ) {
        $this->searchRequestName = $searchRequestName;
        $this->searchResultFactory = $searchResultFactory ?: ObjectManager::getInstance()
            ->get(SearchResultFactory::class);
        $this->searchCriteriaResolverFactory = $searchCriteriaResolverFactory ?: ObjectManager::getInstance()
            ->get(SearchCriteriaResolverFactory::class);
        $this->searchResultApplierFactory = $searchResultApplierFactory ?: ObjectManager::getInstance()
            ->get(SearchResultApplierFactory::class);
        $this->totalRecordsResolverFactory = $totalRecordsResolverFactory ?: ObjectManager::getInstance()
            ->get(TotalRecordsResolverFactory::class);
        $this->engineResolver = $engineResolver ?: ObjectManager::getInstance()
            ->get(EngineResolverInterface::class);
        $this->defaultFilterStrategyApplyChecker = $defaultFilterStrategyApplyChecker ?: ObjectManager::getInstance()
            ->get(DefaultFilterStrategyApplyChecker::class);
        $this->advancedSearchResource = $advancedSearchResource ?: ObjectManager::getInstance()
            ->get(Advanced::class);
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection,
            $productLimitationFactory,
            $metadataPool
        );
    }

    /**
     * Add not indexable fields to search
     *
     * @param array $fields
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFieldsToFilter($fields)
    {
        if ($fields) {
            $this->filters = array_replace_recursive($this->filters, $fields);
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.0.2
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        /**
         * This changes need in backward compatible reasons for support dynamic improved algorithm
         * for price aggregation process.
         */
        $this->setSearchOrder($attribute, $dir);
        if ($this->defaultFilterStrategyApplyChecker->isApplicable()) {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.0.2
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        $this->setAttributeFilterData(Category::ENTITY, 'category_ids', $category->getId());
        /**
         * This changes need in backward compatible reasons for support dynamic improved algorithm
         * for price aggregation process.
         */
        if ($this->defaultFilterStrategyApplyChecker->isApplicable()) {
            parent::addCategoryFilter($category);
        } else {
            $this->_productLimitationPrice();
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.0.2
     */
    public function setVisibility($visibility)
    {
        $this->setAttributeFilterData(Product::ENTITY, 'visibility', $visibility);
        /**
         * This changes need in backward compatible reasons for support dynamic improved algorithm
         * for price aggregation process.
         */
        if ($this->defaultFilterStrategyApplyChecker->isApplicable()) {
            parent::setVisibility($visibility);
        }

        return $this;
    }

    /**
     * Set sort order for search query.
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    private function setSearchOrder($field, $direction)
    {
        $field = (string)$this->_getMappedField($field);
        $direction = strtoupper($direction) == self::SORT_ORDER_ASC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;

        $this->searchOrders[$field] = $direction;
    }

    /**
     * Prepare attribute data to filter.
     *
     * @param string $entityType
     * @param string $attributeCode
     * @param mixed $condition
     * @return $this
     */
    private function setAttributeFilterData(string $entityType, string $attributeCode, $condition): self
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->_eavConfig->getAttribute($entityType, $attributeCode);
        $table = $attribute->getBackend()->getTable();
        $condition = $this->advancedSearchResource->prepareCondition($attribute, $condition);
        $this->addFieldsToFilter([$table => [$attributeCode => $condition]]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        if ($this->isLoaded()) {
            return;
        }
        if ($this->filters) {
            foreach ($this->filters as $attributes) {
                foreach ($attributes as $attributeCode => $attributeValue) {
                    $attributeCode = $this->getAttributeCode($attributeCode);
                    $this->addAttributeToSearch($attributeCode, $attributeValue);
                }
            }
            $searchCriteria = $this->getSearchCriteriaResolver()->resolve();
            try {
                $this->searchResult = $this->getSearch()->search($searchCriteria);
                $this->_totalRecords = $this->getTotalRecordsResolver($this->searchResult)->resolve();
            } catch (EmptyRequestDataException $e) {
                /** @var \Magento\Framework\Api\Search\SearchResultInterface $searchResult */
                $this->searchResult = $this->searchResultFactory->create()->setItems([]);
            } catch (NonExistingRequestNameException $e) {
                $this->_logger->error($e->getMessage());
                throw new LocalizedException(
                    __('An error occurred. For details, see the error log.')
                );
            }
            $this->getSearchResultApplier($this->searchResult)->apply();
        }
        parent::_renderFiltersBefore();
    }

    /**
     * @inheritDoc
     * @since 101.0.4
     */
    public function clear()
    {
        $this->searchResult = null;
        return parent::clear();
    }

    /**
     * @inheritDoc
     * @since 101.0.4
     */
    protected function _reset()
    {
        $this->searchResult = null;
        return parent::_reset();
    }

    /**
     * @inheritdoc
     * @since 101.0.4
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $this->getEntity();

        $this->printLogQuery($printQuery, $logQuery);

        try {
            /**
             * Prepare select query
             * @var string $query
             */
            $query = $this->getSelect();
            $rows = $this->_fetchAll($query);
        } catch (\Exception $e) {
            $this->printLogQuery(false, true, $query ?? null);
            throw $e;
        }

        foreach ($rows as $value) {
            $object = $this->getNewEmptyItem()->setData($value);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = [$object];
            }
        }

        return $this;
    }

    /**
     * Get total records resolver.
     *
     * @param SearchResultInterface $searchResult
     * @return TotalRecordsResolverInterface
     */
    private function getTotalRecordsResolver(SearchResultInterface $searchResult): TotalRecordsResolverInterface
    {
        return $this->totalRecordsResolverFactory->create(
            [
            'searchResult' => $searchResult,
            ]
        );
    }

    /**
     * Get search criteria resolver.
     *
     * @return SearchCriteriaResolverInterface
     */
    private function getSearchCriteriaResolver(): SearchCriteriaResolverInterface
    {
        return $this->searchCriteriaResolverFactory->create(
            [
            'builder' => $this->getSearchCriteriaBuilder(),
            'collection' => $this,
            'searchRequestName' => $this->searchRequestName,
            'currentPage' => $this->_curPage,
            'size' => $this->getPageSize(),
            'orders' => $this->searchOrders,
            ]
        );
    }

    /**
     * Get search result applier.
     *
     * @param SearchResultInterface $searchResult
     * @return SearchResultApplierInterface
     */
    private function getSearchResultApplier(SearchResultInterface $searchResult): SearchResultApplierInterface
    {
        return $this->searchResultApplierFactory->create(
            [
            'collection' => $this,
            'searchResult' => $searchResult,
            /** This variable sets by serOrder method, but doesn't have a getter method. */
            'orders' => $this->_orders,
            'size' => $this->getPageSize(),
            'currentPage' => (int)$this->_curPage,
            ]
        );
    }

    /**
     * Get attribute code.
     *
     * @param string $attributeCode
     * @return string
     */
    private function getAttributeCode($attributeCode)
    {
        if (is_numeric($attributeCode)) {
            $attributeCode = $this->_eavConfig->getAttribute(Product::ENTITY, $attributeCode)
                ->getAttributeCode();
        }

        return $attributeCode;
    }

    /**
     * Create a filter and add it to the SearchCriteriaBuilder.
     *
     * @param string $attributeCode
     * @param array|string $attributeValue
     * @return void
     */
    private function addAttributeToSearch($attributeCode, $attributeValue)
    {
        if (isset($attributeValue['from']) || isset($attributeValue['to'])) {
            $this->addRangeAttributeToSearch($attributeCode, $attributeValue);
        } elseif (!is_array($attributeValue)) {
            $this->getFilterBuilder()->setField($attributeCode)->setValue($attributeValue);
            $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        } elseif (isset($attributeValue['like'])) {
            $this->getFilterBuilder()->setField($attributeCode)->setValue($attributeValue['like']);
            $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        } elseif (isset($attributeValue['in'])) {
            $this->getFilterBuilder()->setField($attributeCode)->setValue($attributeValue['in']);
            $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        } elseif (isset($attributeValue['in_set'])) {
            $this->getFilterBuilder()->setField($attributeCode)->setValue($attributeValue['in_set']);
            $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        }
    }

    /**
     * Add attributes that have a range (from,to) to the SearchCriteriaBuilder.
     *
     * @param string $attributeCode
     * @param array|string $attributeValue
     * @return void
     */
    private function addRangeAttributeToSearch($attributeCode, $attributeValue)
    {
        if (isset($attributeValue['from']) && '' !== $attributeValue['from']) {
            $this->getFilterBuilder()->setField("{$attributeCode}.from")->setValue($attributeValue['from']);
            $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        }
        if (isset($attributeValue['to']) && '' !== $attributeValue['to']) {
            $this->getFilterBuilder()->setField("{$attributeCode}.to")->setValue($attributeValue['to']);
            $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        }
    }

    /**
     * Get search.
     *
     * @return \Magento\Search\Api\SearchInterface
     */
    private function getSearch()
    {
        if (null === $this->search) {
            $this->search = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Search\Api\SearchInterface::class);
        }
        return $this->search;
    }

    /**
     * Get search criteria builder.
     *
     * @return SearchCriteriaBuilder
     */
    private function getSearchCriteriaBuilder()
    {
        if (null === $this->searchCriteriaBuilder) {
            $this->searchCriteriaBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

    /**
     * Get filter builder.
     *
     * @return FilterBuilder
     */
    private function getFilterBuilder()
    {
        if (null === $this->filterBuilder) {
            $this->filterBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\FilterBuilder::class);
        }
        return $this->filterBuilder;
    }
}
