<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Model\SourceCarrierLinkManagementInterface;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink as SourceCarrierLinkResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\Collection;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * @inheritdoc
 */
class SourceCarrierLinkManagement implements SourceCarrierLinkManagementInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SourceCarrierLinkResourceModel
     */
    private $sourceCarrierLinkResource;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $carrierLinkCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SourceCarrierLinkResourceModel $sourceCarrierLinkResource
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $carrierLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SourceCarrierLinkResourceModel $sourceCarrierLinkResource,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $carrierLinkCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->sourceCarrierLinkResource = $sourceCarrierLinkResource;
        $this->collectionProcessor = $collectionProcessor;
        $this->carrierLinkCollectionFactory = $carrierLinkCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function saveCarrierLinksBySource(SourceInterface $source): void
    {
        $this->deleteCurrentCarrierLinks($source);

        $carrierLinks = $source->getCarrierLinks();
        if (null !== $carrierLinks && count($carrierLinks)) {
            $this->saveNewCarrierLinks($source);
        }
    }

    /**
     * @param SourceInterface $source
     * @return void
     */
    private function deleteCurrentCarrierLinks(SourceInterface $source)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->resourceConnection->getTableName(SourceCarrierLink::TABLE_NAME_SOURCE_CARRIER_LINK),
            $connection->quoteInto('source_code = ?', $source->getSourceCode())
        );
    }

    /**
     * @param SourceInterface $source
     * @return void
     */
    private function saveNewCarrierLinks(SourceInterface $source)
    {
        $carrierLinkData = [];
        foreach ($source->getCarrierLinks() as $carrierLink) {
            $carrierLinkData[] = [
                'source_code' => $source->getSourceCode(),
                SourceCarrierLinkInterface::CARRIER_CODE => $carrierLink->getCarrierCode(),
                SourceCarrierLinkInterface::POSITION => $carrierLink->getPosition(),
            ];
        }

        $this->resourceConnection->getConnection()->insertMultiple(
            $this->resourceConnection->getTableName(SourceCarrierLink::TABLE_NAME_SOURCE_CARRIER_LINK),
            $carrierLinkData
        );
    }

    /**
     * @inheritdoc
     */
    public function loadCarrierLinksBySource(SourceInterface $source): void
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceCarrierLinkInterface::SOURCE_CODE, $source->getSourceCode())
            ->create();

        /** @var Collection $collection */
        $collection = $this->carrierLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $source->setCarrierLinks($collection->getItems());
    }
}
