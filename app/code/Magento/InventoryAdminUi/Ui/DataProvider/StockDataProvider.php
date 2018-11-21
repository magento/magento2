<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder as SearchSearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Ui\DataProvider\SearchResultFactory;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockDataProvider extends DataProvider
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinks;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $apiSearchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchSearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param StockRepositoryInterface $stockRepository
     * @param SearchResultFactory $searchResultFactory
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchCriteriaBuilder $apiSearchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param array $meta
     * @param array $data
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) All parameters are needed for backward compatibility
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchSearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        StockRepositoryInterface $stockRepository,
        SearchResultFactory $searchResultFactory,
        GetStockSourceLinksInterface $getStockSourceLinks,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $apiSearchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
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
        $this->stockRepository = $stockRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->getStockSourceLinks = $getStockSourceLinks;
        $this->sourceRepository = $sourceRepository;
        $this->apiSearchCriteriaBuilder = $apiSearchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = parent::getData();
        if ('inventory_stock_form_data_source' === $this->name) {
            // It is need for support of several fieldsets.
            // For details see \Magento\Ui\Component\Form::getDataSourceData
            if ($data['totalRecords'] > 0) {
                $stockId = (int)$data['items'][0][StockInterface::STOCK_ID];
                $stockGeneralData = $data['items'][0];
                $dataForSingle[$stockId] = [
                    'general' => $stockGeneralData,
                    'sources' => [
                        'assigned_sources' => $this->getAssignedSourcesData($stockId),
                    ],
                ];
                $data = $dataForSingle;
            } else {
                $data = [];
            }
        } elseif ('inventory_stock_listing_data_stock' === $this->name) {
            if ($data['totalRecords'] > 0) {
                foreach ($data['items'] as $index => $stock) {
                    $data['items'][$index]['assigned_sources'] = $this->getAssignedSourcesById($stock['stock_id']);
                }
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResult()
    {
        $searchCriteria = $this->getSearchCriteria();
        $result = $this->stockRepository->getList($searchCriteria);

        $searchResult = $this->searchResultFactory->create(
            $result->getItems(),
            $result->getTotalCount(),
            $searchCriteria,
            StockInterface::STOCK_ID
        );
        return $searchResult;
    }

    /**
     * @param int $stockId
     * @return array
     */
    private function getAssignedSourcesData(int $stockId): array
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(StockSourceLinkInterface::PRIORITY)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->apiSearchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->addSortOrder($sortOrder)
            ->create();

        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);

        if ($searchResult->getTotalCount() === 0) {
            return [];
        }

        $assignedSourcesData = [];
        foreach ($searchResult->getItems() as $link) {
            $source = $this->sourceRepository->get($link->getSourceCode());

            $assignedSourcesData[] = [
                SourceInterface::NAME => $source->getName(),
                StockSourceLinkInterface::SOURCE_CODE => $link->getSourceCode(),
                StockSourceLinkInterface::STOCK_ID => $link->getStockId(),
                StockSourceLinkInterface::PRIORITY => $link->getPriority(),
            ];
        }
        return $assignedSourcesData;
    }

    /**
     * @param int $stockId
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAssignedSourcesById(int $stockId): array
    {
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        $sourcesData = [];
        foreach ($sources as $source) {
            $sourcesData[] = [
                'sourceCode' => $source->getSourceCode(),
                'name' => $source->getName()
            ];
        }

        return $sourcesData;
    }
}
