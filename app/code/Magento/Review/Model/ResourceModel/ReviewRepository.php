<?php
namespace Magento\Review\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Api\Data\ReviewSearchResultsInterface;
use Magento\Review\Api\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;
    
    /**
     * @var Review\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Review\Api\Data\ReviewSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterfaceFactory
     */
    protected $searchCriteriaFactory;

    /**
     * ReviewRepository constructor.
     * 
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param \Magento\Review\Api\Data\ReviewSearchResultsInterfaceFactory $searchResultsInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Magento\Review\Api\Data\ReviewSearchResultsInterfaceFactory $searchResultsInterface,
        \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    )
    {
        $this->reviewFactory = $reviewFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsInterface;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($reviewId)
    {
        /** @var \Magento\Review\Model\Review $review */
        $review = $this->reviewFactory->create()
            ->load($reviewId);

        if(!$review->getId()) {
            throw new NoSuchEntityException(__("Requested Review doesn't exist"));
        }
        return $review;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Review\Api\Data\ReviewInterface $review)
    {
        /** @var \Magento\Review\Model\Review $review */
        $validationResult = $review->validate();
        if (true !== $validationResult) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Invalid review data: %1', implode(',', $validationResult))
            );
        }
        $review->setEntityId(1); // product
        
        $review->save();
        $review->aggregate();
        return $review;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        foreach($searchCriteria->getFilterGroups() as $filterGroup) {
            $this->addFilterGroupToCollection($filterGroup, $collection);
        }

        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();


        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());


        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magento\Review\Model\ResourceModel\Review\Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Review\Model\ResourceModel\Review\Collection $collection
    ) {
        $fields = [];
        $productFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';

            if ($filter->getField() == 'product_id') {
                $productFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($productFilter) {
            $collection->addEntityFilter('product', $productFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }
    
}