<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Braintree\Model\Report\Row\TransactionMap;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

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
    protected $_itemObjectClass = TransactionMap::class;

    /**
     * @var array
     */
    private $filtersList = [];

    /**
     * @var FilterMapper
     */
    private $filterMapper;

    /**
     * @var BraintreeAdapterFactory
     */
    private $braintreeAdapterFactory;

    /**
     * @var \Braintree\ResourceCollection | null
     */
    private $collection;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param BraintreeAdapter $braintreeAdapter
     * @param FilterMapper $filterMapper
     * @param BraintreeAdapterFactory|null $braintreeAdapterFactory
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        BraintreeAdapter $braintreeAdapter,
        FilterMapper $filterMapper,
        BraintreeAdapterFactory $braintreeAdapterFactory = null
    ) {
        parent::__construct($entityFactory);
        $this->filterMapper = $filterMapper;
        $this->braintreeAdapterFactory = $braintreeAdapterFactory ?
            : ObjectManager::getInstance()->get(BraintreeAdapterFactory::class);
    }

    /**
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function getItems()
    {
        if (!$this->fetchIdsCollection()) {
            return [];
        }

        $result = [];
        $counter = 0;
        $pageSize = $this->getPageSize();
        $skipCounter = ($this->_curPage - 1) * $pageSize;

        // To optimize the processing of large searches, data is retrieved from the server lazily.
        foreach ($this->collection as $item) {
            if ($skipCounter > 0) {
                $skipCounter --;
            } else {
                $entity = $this->_entityFactory->create($this->_itemObjectClass, ['transaction' => $item]);
                if ($entity) {
                    $result[] = $entity;

                    $counter ++;
                    if ($pageSize && $counter >= $pageSize) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fetch collection from Braintree
     * @return \Braintree\ResourceCollection|null
     */
    protected function fetchIdsCollection()
    {
        if (empty($this->filtersList)) {
            return null;
        }

        // Fetch all transaction IDs in order to filter
        if (empty($this->collection)) {
            $filters = $this->getFilters();
            $this->collection = $this->braintreeAdapterFactory->create()
                ->search($filters);
        }

        return $this->collection;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        $collection = $this->fetchIdsCollection();
        return null === $collection ? 0 : $collection->maximumCount();
    }

    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function getPageSize()
    {
        $pageSize = parent::getPageSize();
        return $pageSize === null ? static::TRANSACTION_MAXIMUM_COUNT : $pageSize;
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * Add filter to list
     *
     * @param object $filter
     * @return void
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
