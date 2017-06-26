<?php

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\SourceItem as ResourceSource;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterfaceFactory;
use Psr\Log\LoggerInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;

class SourceItemRepository implements SourceItemRepositoryInterface
{
    /**
     * @var ResourceSource
     */
    private $resourceSource;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var SourceItemSearchResultsInterfaceFactory
     */
    private $sourceItemSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceRepository constructor
     *
     * @param ResourceSource $resourceSource
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param SourceItemSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceSource $resourceSource,
        SourceItemInterfaceFactory $sourceItemFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        SourceItemSearchResultsInterfaceFactory $sourceItemSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->resourceSource = $resourceSource;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->sourceItemSearchResultsFactory = $sourceItemSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @param SourceItemInterface $sourceItem
     * @return int
     * @throws CouldNotSaveException
     */
    public function save(SourceItemInterface $sourceItem)
    {
        try {
            $this->resourceSource->save($sourceItem);
            return $sourceItem->getSourceItemId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save source item'), $e);
        }
    }

    public function get($sourceItemId)
    {
        $sourceItem = $this->sourceItemFactory->create();
        $this->resourceSource->load($sourceItem, $sourceItemId, SourceItemInterface::SOURCE_ITEM_ID);

        if (!$sourceItem->getSourceItemId()) {
            throw NoSuchEntityException::singleField(SourceItemInterface::SOURCE_ITEM_ID, $sourceItemId);
        }

        return $sourceItem;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {

    }

    public function delete($sourceItemId)
    {

    }
}
