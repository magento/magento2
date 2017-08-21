<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Search\Model\ResourceModel\Query\Collection as QueryCollection;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Search\Model\SearchCollectionInterface as Collection;
use Magento\Search\Model\SearchCollectionFactory as CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Search query model
 *
 * @method \Magento\Search\Model\Query setQueryText(string $value)
 * @method int getNumResults()
 * @method \Magento\Search\Model\Query setNumResults(int $value)
 * @method int getPopularity()
 * @method \Magento\Search\Model\Query setPopularity(int $value)
 * @method string getRedirect()
 * @method \Magento\Search\Model\Query setRedirect(string $value)
 * @method int getDisplayInTerms()
 * @method \Magento\Search\Model\Query setDisplayInTerms(int $value)
 * @method \Magento\Search\Model\Query setQueryNameExceeded(bool $value)
 * @method int getIsActive()
 * @method \Magento\Search\Model\Query setIsActive(int $value)
 * @method int getIsProcessed()
 * @method \Magento\Search\Model\Query setIsProcessed(int $value)
 * @method string getUpdatedAt()
 * @method \Magento\Search\Model\Query setUpdatedAt(string $value)
 * @method \Magento\Search\Model\Query setIsQueryTextExceeded(bool $value)
 * @method \Magento\Search\Model\Query setIsQueryTextShort(bool $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class Query extends AbstractModel implements QueryInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'search_query';

    /**
     * Event object key name
     *
     * @var string
     */
    protected $_eventObject = 'search_query';

    const CACHE_TAG = 'SEARCH_QUERY';

    const XML_PATH_MIN_QUERY_LENGTH = 'catalog/search/min_query_length';

    const XML_PATH_MAX_QUERY_LENGTH = 'catalog/search/max_query_length';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Search collection factory
     *
     * @var CollectionFactory
     */
    protected $_searchCollectionFactory;

    /**
     * Query collection factory
     *
     * @var QueryCollectionFactory
     */
    protected $_queryCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param CollectionFactory $searchCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        QueryCollectionFactory $queryCollectionFactory,
        CollectionFactory $searchCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_searchCollectionFactory = $searchCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Search\Model\ResourceModel\Query::class);
    }

    /**
     * Retrieve search collection
     *
     * @return Collection
     */
    public function getSearchCollection()
    {
        return $this->_searchCollectionFactory->create();
    }

    /**
     * Retrieve collection of suggest queries
     *
     * @return QueryCollection
     */
    public function getSuggestCollection()
    {
        $collection = $this->getData('suggest_collection');
        if ($collection === null) {
            $collection = $this->_queryCollectionFactory->create()->setStoreId(
                $this->getStoreId()
            )->setQueryFilter(
                $this->getQueryText()
            );
            $this->setData('suggest_collection', $collection);
        }
        return $collection;
    }

    /**
     * Load Query object by query string
     *
     * @param string $text
     * @return $this
     * @deprecated 100.1.0 "synonym for" feature has been removed
     */
    public function loadByQuery($text)
    {
        $this->loadByQueryText($text);
        return $this;
    }

    /**
     * Load Query object only by query text
     *
     * @param string $text
     * @return $this
     */
    public function loadByQueryText($text)
    {
        $this->_getResource()->loadByQueryText($this, $text);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
    }

    /**
     * Retrieve store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (!($storeId = $this->getData('store_id'))) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * Prepare save query for result
     *
     * @return $this
     */
    public function prepare()
    {
        if (!$this->getId()) {
            $this->setIsActive(0);
            $this->setIsProcessed(0);
            $this->save();
            $this->setIsActive(1);
        }

        return $this;
    }

    /**
     * Save query with incremental popularity
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveIncrementalPopularity()
    {
        $this->getResource()->saveIncrementalPopularity($this);

        return $this;
    }

    /**
     * Save query with number of results
     *
     * @param int $numResults
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveNumResults($numResults)
    {
        $this->setNumResults($numResults);
        $this->getResource()->saveNumResults($this);

        return $this;
    }

    /**
     * Retrieve minimum query length
     *
     * @return int
     */
    public function getMinQueryLength()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_MIN_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Retrieve maximum query length
     *
     * @return int
     */
    public function getMaxQueryLength()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_MAX_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getQueryText()
    {
        return $this->getDataByKey('query_text');
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function isQueryTextExceeded()
    {
        return $this->getData('is_query_text_exceeded');
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     * @since 100.1.0
     */
    public function isQueryTextShort()
    {
        return $this->getData('is_query_text_short');
    }
}
