<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackSearchResultInterfaceFactory;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Spi\ShipmentTrackResourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Repository of shipment tracking information
 */
class TrackRepository implements ShipmentTrackRepositoryInterface
{
    /**
     * @var ShipmentTrackResourceInterface
     */
    private $trackResource;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    private $trackFactory;

    /**
     * @var ShipmentTrackSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $shipmentCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ShipmentTrackResourceInterface $trackResource
     * @param ShipmentTrackInterfaceFactory $trackFactory
     * @param ShipmentTrackSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory|null $shipmentCollection
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ShipmentTrackResourceInterface $trackResource,
        ShipmentTrackInterfaceFactory $trackFactory,
        ShipmentTrackSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        ?CollectionFactory $shipmentCollection = null,
        ?LoggerInterface $logger = null
    ) {
        $this->trackResource = $trackResource;
        $this->trackFactory = $trackFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->shipmentCollection = $shipmentCollection ?:
            ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->logger = $logger ?:
            ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $entity = $this->trackFactory->create();
        $this->trackResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(ShipmentTrackInterface $entity)
    {
        try {
            $this->trackResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the shipment tracking.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(ShipmentTrackInterface $entity)
    {
        $shipments = $this->shipmentCollection->create()
            ->addFieldToFilter('order_id', $entity['order_id'])
            ->addFieldToFilter('entity_id', $entity['parent_id'])
            ->toArray();

        if (empty($shipments['items'])) {
            $this->logger->error('The shipment doesn\'t belong to the order.');
            throw new CouldNotSaveException(__('Could not save the shipment tracking.'));
        }

        try {
            $this->trackResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the shipment tracking.'), $e);
        }
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);
        return $this->delete($entity);
    }
}
