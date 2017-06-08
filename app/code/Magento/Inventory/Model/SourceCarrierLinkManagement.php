<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Model\AbstractModel;
use Magento\Inventory\Model\ResourceModel\Source as ResourceSource;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink as ResourceSourceCarrierLink;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\CollectionFactory as CarrierLinkCollectionFactory;
use Magento\Inventory\Setup\InstallSchema;
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
    private $connection;

    /**
     * @var ResourceSourceCarrierLink
     */
    private $resourceSourceCarrierLink;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CarrierLinkCollectionFactory
     */
    private $carrierLinkCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * SourceCarrierLinkManagement constructor
     *
     * @param ResourceConnection $connection
     * @param ResourceSourceCarrierLink $resourceSourceCarrierLink
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CarrierLinkCollectionFactory $carrierLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceConnection $connection,
        ResourceSourceCarrierLink $resourceSourceCarrierLink,
        CollectionProcessorInterface $collectionProcessor,
        CarrierLinkCollectionFactory $carrierLinkCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->connection = $connection;
        $this->resourceSourceCarrierLink = $resourceSourceCarrierLink;
        $this->collectionProcessor = $collectionProcessor;
        $this->carrierLinkCollectionFactory = $carrierLinkCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function saveCarrierLinksBySource(SourceInterface $source)
    {
        if (is_array($source->getCarrierLinks())) {
            try {
                $this->deleteCurrentCarrierLinks($source);
                if (!empty($source->getCarrierLinks())) {
                    $this->saveNewCarrierLinks($source);
                }
            } catch (\Exception $e) {
                throw new StateException(__('Could not update Carrier Links'), $e);
            }
        }
    }

    /**
     * @param SourceInterface $source
     * @return void
     */
    private function deleteCurrentCarrierLinks(SourceInterface $source)
    {
        $connection = $this->connection->getConnection();
        $connection->delete(
            $connection->getTableName(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK),
            $connection->quoteInto('source_id = ?', $source->getSourceId())
        );
    }

    /**
     * @param SourceInterface $source
     * @return void
     */
    private function saveNewCarrierLinks(SourceInterface $source)
    {
        $carrierLinkData = [];
        /** @var SourceCarrierLinkInterface|AbstractModel $carrierLink */
        foreach ($source->getCarrierLinks() as $carrierLink) {
            $carrierLinkData[] = [
                'source_id' => $source->getSourceId(),
                SourceCarrierLinkInterface::CARRIER_CODE => $carrierLink->getCarrierCode(),
                SourceCarrierLinkInterface::POSITION => $carrierLink->getPosition(),
            ];
        }

        $connection = $this->connection->getConnection();
        $connection->insertMultiple(
            $connection->getTableName(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK),
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

        /** @var ResourceSourceCarrierLink\Collection $collection */
        $collection = $this->carrierLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $source->setCarrierLinks($collection->getItems());
    }
}
