<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId): array
    {
        try {
            $sourceIds = $this->getAssignedSourceIds($stockId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceInterface::SOURCE_ID, $sourceIds, 'in')
                ->create();
            $searchResult = $this->sourceRepository->getList($searchCriteria);
            return $searchResult->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Sources for Stock'), $e);
        }
    }

    /**
     * Get all linked SourceIds by given stockId
     *
     * @param int $stockId
     * @return array
     */
    private function getAssignedSourceIds(int $stockId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection
            ->select()
            ->from(
                $this->resourceConnection->getTableName(StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK),
                [StockSourceLink::SOURCE_ID]
            )
            ->where(StockSourceLink::STOCK_ID . ' = ?', $stockId);
        return $connection->fetchCol($select);
    }
}
