<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\LoginAsCustomerLog\Api\Data\LogInterface;
use Magento\LoginAsCustomerLog\Api\GetLogsListInterface;
use Magento\Ui\DataProvider\SearchResultFactory;

/**
 * @inheritDoc
 */
class LogDataProvider extends DataProvider
{
    /**
     * @var GetLogsListInterface
     */
    private $getLogsList;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param GetLogsListInterface $getLogsList
     * @param SearchResultFactory $searchResultFactory
     * @param SortOrderBuilder $sortOrderBuilder
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        GetLogsListInterface $getLogsList,
        SearchResultFactory $searchResultFactory,
        SortOrderBuilder $sortOrderBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->getLogsList = $getLogsList;
        $this->searchResultFactory = $searchResultFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getSearchResult()
    {
        $searchCriteria = $this->getSearchCriteria();
        $sortOrders = $searchCriteria->getSortOrders();
        $sortOrder = current($sortOrders);
        if (!$sortOrder->getField()) {
            $sortOrder = $this->sortOrderBuilder->setDescendingDirection()->setField(LogInterface::TIME)->create();
            $searchCriteria->setSortOrders([$sortOrder]);
        }
        $result = $this->getLogsList->execute($searchCriteria);

        return $this->searchResultFactory->create(
            $result->getItems(),
            $result->getTotalCount(),
            $searchCriteria,
            LogInterface::LOG_ID
        );
    }
}
