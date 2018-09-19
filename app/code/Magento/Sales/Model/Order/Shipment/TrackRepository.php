<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * @param ShipmentTrackResourceInterface $trackResource
     * @param ShipmentTrackInterfaceFactory $trackFactory
     * @param ShipmentTrackSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ShipmentTrackResourceInterface $trackResource,
        ShipmentTrackInterfaceFactory $trackFactory,
        ShipmentTrackSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {

        $this->trackResource = $trackResource;
        $this->trackFactory = $trackFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
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
