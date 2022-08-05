<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\LoginAsCustomerLog\Api\Data\LogSearchResultsInterface;
use Magento\LoginAsCustomerLog\Api\Data\LogSearchResultsInterfaceFactory;
use Magento\LoginAsCustomerLog\Api\GetLogsListInterface;
use Magento\LoginAsCustomerLog\Model\ResourceModel\Log\CollectionFactory;

/**
 * @inheritDoc
 */
class GetLogList implements GetLogsListInterface
{
    /**
     * @var CollectionFactory
     */
    private $logCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var LogSearchResultsInterfaceFactory
     */
    private $logSearchResultsFactory;

    /**
     * @param CollectionFactory $logCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param LogSearchResultsInterfaceFactory $logSearchResultsFactory
     */
    public function __construct(
        CollectionFactory $logCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        LogSearchResultsInterfaceFactory $logSearchResultsFactory
    ) {
        $this->logCollectionFactory = $logCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->logSearchResultsFactory = $logSearchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria): LogSearchResultsInterface
    {
        $collection = $this->logCollectionFactory->create();
        $searchCriteria = $searchCriteria ?: $this->searchCriteriaBuilder->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult = $this->logSearchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
