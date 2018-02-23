<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\Source;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * @inheritdoc
 */
class GetAssignedSourcesForStock implements GetAssignedSourcesForStockInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param SortOrderBuilder $sortOrderBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        GetStockSourceLinksInterface $getStockSourceLinks,
        SortOrderBuilder $sortOrderBuilder,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->getStockSourceLinks = $getStockSourceLinks;
        $this->logger = $logger;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId): array
    {
        try {
            $sourceCodes = $this->getAssignedSourceCodes($stockId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceInterface::SOURCE_CODE, $sourceCodes, 'in')
                ->addFilter(SourceInterface::ENABLED, true)
                ->create();

            $searchResult = $this->sourceRepository->getList($searchCriteria);
            $items = $searchResult->getItems();
            $this->sortByCodes($items, $sourceCodes);
            return $items;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Sources for Stock'), $e);
        }
    }

    /**
     * TODO: this is a temporary decision, remove it!
     *
     * @param Source[] $items
     * @param string[] $codes
     * @return bool
     */
    private function sortByCodes(array &$items, array $codes)
    {
        return uasort(
            $items,
            function (Source $a, Source $b) use ($codes) {
                return array_search($a->getSourceCode(), $codes) > array_search($b->getSourceCode(),  $codes);
            }
        );
    }

    /**
     * Get all linked SourceCodes by given stockId
     *
     * @param int $stockId
     * @return array
     */
    private function getAssignedSourceCodes(int $stockId): array
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(StockSourceLinkInterface::PRIORITY)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->addSortOrder($sortOrder)
            ->create();
        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);

        if (0 === $searchResult->getTotalCount()) {
            return [];
        }

        $sourceCodes = [];
        foreach ($searchResult->getItems() as $link) {
            $sourceCodes[] = $link->getSourceCode();
        }
        return $sourceCodes;
    }
}
