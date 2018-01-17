<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventoryApi\Api\GetSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class GetAssignedSourcesForStock implements GetAssignedSourcesForStockInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var GetSourceLinksInterface
     */
    private $getSourceLinks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetSourceLinksInterface $getSourceLinks
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        GetSourceLinksInterface $getSourceLinks,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->getSourceLinks = $getSourceLinks;
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

        $searchResult = $this->getSourceLinks->execute($searchCriteria);

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
