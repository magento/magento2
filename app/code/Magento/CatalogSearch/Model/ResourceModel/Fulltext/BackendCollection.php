<?php
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogUrlRewrite\Model\Storage\DbStorage;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\Search\Search;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class BackendCollection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;
    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var SearchResultApplierFactory
     */
    private SearchResultApplierFactory $searchResultApplierFactory;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultApplierFactory $searchResultApplierFactory
     * @param Search $search
     * @param AdapterInterface|null $connection
     * @param ProductLimitationFactory|null $productLimitationFactory
     * @param MetadataPool|null $metadataPool
     * @param TableMaintainer|null $tableMaintainer
     * @param PriceTableResolver|null $priceTableResolver
     * @param DimensionFactory|null $dimensionFactory
     * @param Category|null $categoryResourceModel
     * @param DbStorage|null $urlFinder
     * @param GalleryReadHandler|null $productGalleryReadHandler
     * @param Gallery|null $mediaGalleryResource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory    $entityFactory,
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
        \Magento\Framework\Stdlib\DateTime                  $dateTime,
        GroupManagementInterface $groupManagement,
        FilterBuilder                                       $filterBuilder,
        SortOrderBuilder                                    $sortOrderBuilder,
        FilterGroupBuilder                                  $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultApplierFactory $searchResultApplierFactory,
        private readonly Search   $search,
        \Magento\Framework\DB\Adapter\AdapterInterface      $connection = null,
        ProductLimitationFactory $productLimitationFactory = null,
        MetadataPool $metadataPool = null,
        TableMaintainer $tableMaintainer = null,
        PriceTableResolver $priceTableResolver = null,
        DimensionFactory $dimensionFactory = null,
        Category $categoryResourceModel = null,
        DbStorage $urlFinder = null,
        GalleryReadHandler $productGalleryReadHandler = null,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $mediaGalleryResource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource, $eavEntityFactory, $resourceHelper, $universalFactory, $storeManager, $moduleManager, $catalogProductFlatState, $scopeConfig, $productOptionFactory, $catalogUrl, $localeDate, $customerSession, $dateTime, $groupManagement, $connection, $productLimitationFactory, $metadataPool, $tableMaintainer, $priceTableResolver, $dimensionFactory, $categoryResourceModel, $urlFinder, $productGalleryReadHandler, $mediaGalleryResource);
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultApplierFactory = $searchResultApplierFactory;
    }

    /**
     * Add a filter onto the collection
     *
     * @param string $attribute
     * @param mixed $condition
     *
     * @return void
     */
    public function addFieldToFilter($attribute, $condition = null): void
    {
        $this->filterBuilder->setField($attribute);
        $this->filterBuilder->setValue($condition);
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
    }

    /**
     * Setup a search term filter and submit the search to ElasticSearch
     *
     * @param string $fulltext
     *
     * @return void
     */
    public function addSearchFilter(string $fulltext): void
    {
        $this->addFieldToFilter('search_term', $fulltext);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setRequestName('admin_search_container');

        $sort = $this->sortOrderBuilder->setField('relevance')->setDescendingDirection()->create();
        $searchCriteria->setSortOrders([$sort]);
        $result = $this->search->search($searchCriteria);
        $this->getSearchResultApplier($result, $searchCriteria)->apply();
    }

    /**
     * Assign the result from ElasticSearch to the collection
     *
     * @param SearchResultInterface $searchResult
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultApplierInterface
     */
    private function getSearchResultApplier(
        SearchResultInterface $searchResult,
        SearchCriteriaInterface $searchCriteria
    ): SearchResultApplierInterface {
        $sort = current($searchCriteria->getSortOrders());

        return $this->searchResultApplierFactory->create(
            [
                'collection' => $this,
                'searchResult' => $searchResult,
                /** This variable sets by serOrder method, but doesn't have a getter method. */
                'orders' => [$sort->getField(), $sort->getDirection()],
                'size' => $searchCriteria->getPageSize(),
                'currentPage' => $searchCriteria->getPageSize(),
            ]
        );
    }
}
