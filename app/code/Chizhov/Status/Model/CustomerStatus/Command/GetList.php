<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

use Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface;
use Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterfaceFactory;
use Chizhov\Status\Model\ResourceModel\CustomerStatus\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class GetList implements GetListInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var \Chizhov\Status\Model\ResourceModel\CustomerStatus\CollectionFactory
     */
    protected $customerStatusCollectionFactory;

    /**
     * @var \Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterfaceFactory
     */
    protected $customerStatusSearchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * GetList constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Chizhov\Status\Model\ResourceModel\CustomerStatus\CollectionFactory $customerStatusCollectionFactory
     * @param \Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterfaceFactory $customerStatusSearchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $customerStatusCollectionFactory,
        CustomerStatusSearchResultsInterfaceFactory $customerStatusSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->customerStatusCollectionFactory = $customerStatusCollectionFactory;
        $this->customerStatusSearchResultsFactory = $customerStatusSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): CustomerStatusSearchResultsInterface
    {
        /** @var \Chizhov\Status\Model\ResourceModel\CustomerStatus\Collection $collection */
        $collection = $this->customerStatusCollectionFactory->create();
        $searchCriteria = $searchCriteria ?: $this->searchCriteriaBuilder->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var CustomerStatusSearchResultsInterface $searchResult */
        $searchResult = $this->customerStatusSearchResultsFactory->craete();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
