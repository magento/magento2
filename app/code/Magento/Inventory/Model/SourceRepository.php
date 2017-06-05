<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Inventory\Model\Resource\Source as ResourceSource;
use Magento\Inventory\Model\Resource\Source\CollectionFactory;
use Magento\Inventory\Model\Resource\SourceCarrierLink as ResourceSourceCarrierLink;
use Magento\Inventory\Model\Resource\SourceCarrierLink\CollectionFactory as CarrierLinkCollectionFactory;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SourceRepository
 *
 * Provides implementation of CQRS for sourcemodel
 *
 */
class SourceRepository implements SourceRepositoryInterface
{
    /**
     * @var ResourceSource
     */
    private $resourceSource;

    /**
     * @var ResourceSourceCarrierLink
     */
    private $resourceSourceCarrierLink;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CarrierLinkCollectionFactory
     */
    private $carrierLinkCollectionFactory;

    /**
     * @var SourceSearchResultsInterfaceFactory
     */
    private $sourceSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceRepository constructor.
     * @param ResourceSource $resourceSource
     * @param ResourceSourceCarrierLink $resourceSourceCarrierLink
     * @param SourceInterfaceFactory $sourceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param CarrierLinkCollectionFactory $carrierLinkCollectionFactory
     * @param SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceSource $resourceSource,
        ResourceSourceCarrierLink $resourceSourceCarrierLink,
        SourceInterfaceFactory $sourceFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        CarrierLinkCollectionFactory $carrierLinkCollectionFactory,
        SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->resourceSource = $resourceSource;
        $this->resourceSourceCarrierLink = $resourceSourceCarrierLink;
        $this->sourceFactory = $sourceFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->carrierLinkCollectionFactory = $carrierLinkCollectionFactory;
        $this->sourceSearchResultsFactory = $sourceSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(SourceInterface $source)
    {
        try {
            /** @var SourceInterface|AbstractModel $source */
            $this->resourceSource->save($source);

            if ($source->getCarrierLinks() === null) {
                return $source->getSourceId();
            }

            // clean up for the old carrier links
            $this->resourceSourceCarrierLink->deleteBySource($source);

            /** @var SourceCarrierLinkInterface|AbstractModel $carrierLink */
            foreach ($source->getCarrierLinks() as $carrierLink) {
                $carrierLink->setData(SourceInterface::SOURCE_ID, $source->getSourceId());
                $this->resourceSourceCarrierLink->save($carrierLink);
            }

            return $source->getSourceId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new CouldNotSaveException(__('Could not save source'));
        }
    }

    /**
     * @inheritdoc
     */
    public function get($sourceId)
    {
        /** @var SourceInterface|AbstractModel $source */
        $source = $this->sourceFactory->create();
        $this->resourceSource->load($source, $sourceId, SourceInterface::SOURCE_ID);

        $carrierLinks = $this->loadCarrierLinksBySource($source);
        $source->setCarrierLinks($carrierLinks);

        if (!$source->getSourceId()) {
            throw NoSuchEntityException::singleField(SourceInterface::SOURCE_ID, $sourceId);
        }

        return $source;
    }

    /**
     * Loads the carrier links by given source.
     *
     * @param SourceInterface $source
     *
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[]
     */
    private function loadCarrierLinksBySource(SourceInterface $source)
    {
        /** @var ResourceSourceCarrierLink\Collection $collection */
        $collection = $this->carrierLinkCollectionFactory->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceInterface::SOURCE_ID, $source->getSourceId())
            ->create();

        $this->collectionProcessor->process($searchCriteria, $collection);
        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var \Magento\Inventory\Model\Resource\Source\Collection $collection */
        $collection = $this->collectionFactory->create();

        /** @var SourceSearchResultsInterface $searchResults */
        $searchResults = $this->sourceSearchResultsFactory->create();

        // if there is a searchCriteria defined, use it to add its creterias to the collection
        if (!is_null($searchCriteria)) {
            $this->collectionProcessor->process($searchCriteria, $collection);
            $searchResults->setSearchCriteria($searchCriteria);
        }

        /** @var \Magento\InventoryApi\Api\Data\SourceInterface[] $sourceItems */
        $sourceItems = $collection->getItems();
        foreach ($sourceItems as $sourceItem) {
            $sourceItem->setCarrierLinks($this->loadCarrierLinksBySource($sourceItem));
        }

        $searchResults->setItems($sourceItems);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
