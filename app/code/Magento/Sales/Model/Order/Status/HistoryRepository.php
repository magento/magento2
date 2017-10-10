<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Status;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterfaceFactory;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Spi\OrderStatusHistoryResourceInterface;

/**
 * @since 2.2.0
 */
class HistoryRepository implements OrderStatusHistoryRepositoryInterface
{
    /**
     * @var OrderStatusHistoryResourceInterface
     */
    private $historyResource;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $historyFactory;

    /**
     * @var OrderStatusHistorySearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param OrderStatusHistoryResourceInterface $historyResource
     * @param OrderStatusHistoryInterfaceFactory $historyFactory
     * @param OrderStatusHistorySearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        OrderStatusHistoryResourceInterface $historyResource,
        OrderStatusHistoryInterfaceFactory $historyFactory,
        OrderStatusHistorySearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {

        $this->historyResource = $historyResource;
        $this->historyFactory = $historyFactory;
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
        $entity = $this->historyFactory->create();
        $this->historyResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(OrderStatusHistoryInterface $entity)
    {
        try {
            $this->historyResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the order status history.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(OrderStatusHistoryInterface $entity)
    {
        try {
            $this->historyResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the order status history.'), $e);
        }
        return $entity;
    }
}
