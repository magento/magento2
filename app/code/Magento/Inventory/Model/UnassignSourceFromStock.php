<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\SourceStockLink as SourceStockLinkResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\Collection;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\CollectionFactory;
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class UnassignSourceFromStock implements UnassignSourceFromStockInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $stockLinkCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceStockLinkResourceModel
     */
    private $sourceStockLinkResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $stockLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceStockLinkResourceModel $sourceStockLinkResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $stockLinkCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceStockLinkResourceModel $sourceStockLinkResource,
        LoggerInterface $logger
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->stockLinkCollectionFactory = $stockLinkCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceStockLinkResource = $sourceStockLinkResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute($stockId, $sourceId)
    {
        if (0 === (int)$stockId || 0 === (int)$sourceId) {
            throw new InputException(__('Input data is invalid'));
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceStockLink::STOCK_ID, (int)$stockId)
            ->addFilter(SourceStockLink::SOURCE_ID, $sourceId)
            ->create();

        /** @var Collection $collection */
        $collection = $this->stockLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $items = $collection->getItems();

        if (empty($items)) {
            throw new CouldNotDeleteException(
                __('Source Stock Link is missed (Stock id: %1, Source id: %2 )', $stockId, $sourceId)
            );
        }

        try {
            $sourceStockLink = reset($items);
            $this->sourceStockLinkResource->delete($sourceStockLink);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Source Stock Link'), $e);
        }
    }
}
