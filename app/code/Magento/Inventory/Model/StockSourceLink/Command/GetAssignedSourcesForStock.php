<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;

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
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        GetStockSourceLinksInterface $getStockSourceLinks,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->getStockSourceLinks = $getStockSourceLinks;
        $this->logger = $logger;
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
                ->create();

            $searchResult = $this->sourceRepository->getList($searchCriteria);

            return $searchResult->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Sources for Stock'), $e);
        }
    }

    /**
     * Get all linked SourceCodes by given stockId
     *
     * @param int $stockId
     * @return array
     */
    private function getAssignedSourceCodes(int $stockId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();
        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);
        $links = $searchResult->getItems();
        if (!$links) {
            return [];
        }
        $sourceCodes = [];
        foreach ($links as $link) {
            $sourceCodes[] = $link->getSourceCode();
        }

        return $sourceCodes;
    }
}
