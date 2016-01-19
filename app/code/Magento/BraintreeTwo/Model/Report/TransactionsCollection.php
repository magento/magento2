<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report;

use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TransactionsCollection
 */
class TransactionsCollection extends Collection implements SearchResultInterface
{
    /**
     * Transaction maximum count
     */
    const TRANSACTION_MAXIMUM_COUNT = 100;

    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = 'Magento\BraintreeTwo\Model\Report\Row\TransactionMap';

    /**
     * @var array
     */
    private $filtersList = [];

    /**
     * @var FilterMapper
     */
    private $filterMapper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BraintreeAdapter
     */
    private $braintreeAdapter;

    /**
     * @var \Braintree\ResourceCollection | null
     */
    private $collection;

    /**
     * @param Collection\EntityFactoryInterface $entityFactory
     * @param BraintreeAdapter $braintreeAdapter
     * @param FilterMapper $filterMapper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Collection\EntityFactoryInterface $entityFactory,
        BraintreeAdapter $braintreeAdapter,
        FilterMapper $filterMapper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($entityFactory);
        $this->filterMapper = $filterMapper;
        $this->storeManager = $storeManager;
        $this->braintreeAdapter = $braintreeAdapter;
    }

    /**
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function getItems()
    {
        if (empty($this->filtersList)) {
            return [];
        }

        // Fetch all IDs in order to filter
        $this->collection = $this->braintreeAdapter->search($this->getFilters());

        $result = [];
        $counter = 0;
        // To optimize the processing of large searches, data is retrieved from the server lazily.
        foreach ($this->collection as $item) {
            $entity = $this->_entityFactory->create($this->_itemObjectClass, ['transaction' => $item]);
            if ($entity) {
                $result[] = $entity;

                $counter ++;
                if ($counter >= self::TRANSACTION_MAXIMUM_COUNT) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return null;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return null === $this->collection ? 0 : $this->collection->maximumCount();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition)
    {
        if (is_array($field)) {
            return $this;
        }

        if (!is_array($condition)) {
            $condition = ['eq' => $condition];
        }

        $this->addFilterToList($this->filterMapper->getFilter($field, $condition));

        return $this;
    }

    /**
     * @param object $filter
     */
    private function addFilterToList($filter)
    {
        if (null !== $filter) {
            $this->filtersList[] = $filter;
        }
    }

    /**
     * @return array
     */
    private function getFilters()
    {
        return $this->filtersList;
    }
}
