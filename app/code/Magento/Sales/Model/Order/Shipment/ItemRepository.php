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
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\Data\ShipmentItemInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemSearchResultInterfaceFactory;
use Magento\Sales\Api\ShipmentItemRepositoryInterface;
use Magento\Sales\Model\Spi\ShipmentItemResourceInterface;

/**
 * @since 2.2.0
 */
class ItemRepository implements ShipmentItemRepositoryInterface
{
    /**
     * @var ShipmentItemResourceInterface
     */
    private $itemResource;

    /**
     * @var ShipmentItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ShipmentItemSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ShipmentItemResourceInterface $itemResource
     * @param ShipmentItemInterfaceFactory $itemFactory
     * @param ShipmentItemSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ShipmentItemResourceInterface $itemResource,
        ShipmentItemInterfaceFactory $itemFactory,
        ShipmentItemSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->itemResource = $itemResource;
        $this->itemFactory = $itemFactory;
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
        $entity = $this->itemFactory->create();
        $this->itemResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(ShipmentItemInterface $entity)
    {
        try {
            $this->itemResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the shipment item.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(ShipmentItemInterface $entity)
    {
        try {
            $this->itemResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the shipment item.'), $e);
        }
        return $entity;
    }
}
