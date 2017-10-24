<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink as SourceCarrierLinkResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\Collection;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Shipping\Model\Config;

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
     * Shipping config
     *
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SourceCarrierLinkResourceModel $sourceCarrierLinkResource
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $carrierLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $shippingConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SourceCarrierLinkResourceModel $sourceCarrierLinkResource,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $carrierLinkCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $shippingConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->sourceCarrierLinkResource = $sourceCarrierLinkResource;
        $this->collectionProcessor = $collectionProcessor;
        $this->carrierLinkCollectionFactory = $carrierLinkCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @inheritdoc
     */
    public function saveCarrierLinksBySource(SourceInterface $source)
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
            $connection->quoteInto('source_id = ?', $source->getSourceId())
        );
    }

    /**
     * @param SourceInterface $source
     * @return void
     * @throws ValidationException
     */
    private function saveNewCarrierLinks(SourceInterface $source)
    {
        $carrierLinkData = [];

        $availableCarriers = $this->shippingConfig->getAllCarriers();

        foreach ($source->getCarrierLinks() as $carrierLink) {
            $carrierCode = $carrierLink->getCarrierCode();

            if (array_key_exists($carrierCode, $availableCarriers) === false) {
                throw new ValidationException(__('CarrierCode %1 dos not exists', $carrierCode));
            }

            $carrierLinkData[] = [
                'source_id' => $source->getSourceId(),
                SourceCarrierLinkInterface::CARRIER_CODE => $carrierCode,
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
    public function loadCarrierLinksBySource(SourceInterface $source)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceInterface::SOURCE_ID, $source->getSourceId())
            ->create();

        /** @var Collection $collection */
        $collection = $this->carrierLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $source->setCarrierLinks($collection->getItems());
    }
}
