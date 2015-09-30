<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Repository class for @see \Magento\Sales\Api\Data\OrderItemInterface
 */
class ItemRepository implements \Magento\Sales\Api\OrderItemRepositoryInterface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Metadata
     */
    protected $metadata;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var \Magento\Sales\Model\Order\Item\ProcessorInterface[]
     */
    protected $processorPool;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    protected $registry = [];

    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Sales\Model\Resource\Metadata $metadata
     * @param \Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory $searchResultFactory
     * @param array $processorPool
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Sales\Model\Resource\Metadata $metadata,
        \Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory $searchResultFactory,
        array $processorPool = []
    ) {
        $this->objectFactory = $objectFactory;
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->processorPool = $processorPool;
    }

    /**
     * load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            $orderItem = $this->metadata->getNewInstance();
            $this->metadata->getMapper()->load($orderItem, $id);
            if (!$orderItem->getItemId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }

            $this->addProductOptions($orderItem);
            $this->registry[$id] = $orderItem;
        }
        return $this->registry[$id];
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteria  $criteria
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria)
    {
        /** @var \Magento\Sales\Api\Data\OrderItemSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($criteria);

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $searchResult->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        foreach ($searchResult->getItems() as $item) {
            $this->addProductOptions($item);
        }

        return $searchResult;
    }

    /**
     * Register entity to delete
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderItemInterface $entity)
    {
        $this->metadata->getMapper()->delete($entity);
        unset($this->registry[$entity->getEntityId()]);
        return true;
    }

    /**
     * Delete entity by Id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);
        return $this->delete($entity);
    }

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $entity
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function save(\Magento\Sales\Api\Data\OrderItemInterface $entity)
    {
        // TODO:

        $request = $this->getBuyRequest($entity);
        $entity->setProductOptions($request->toArray());

        $this->metadata->getMapper()->save($entity);
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Add buy request to order item's product options
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return $this
     */
    protected function addProductOptions(\Magento\Sales\Api\Data\OrderItemInterface $orderItem)
    {
        $productType = $orderItem->getProductType();
        if (isset($this->processorPool[$productType])) {
            $this->processorPool[$productType]->processOptions($orderItem);
        }

        if (isset($this->processorPool['custom_options'])) {
            $this->processorPool['custom_options']->processOptions($orderItem);
        }

        return $this;
    }

    /**
     * Retrieve order item's buy request
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $entity
     * @return \Magento\Framework\DataObject
     */
    protected function getBuyRequest(\Magento\Sales\Api\Data\OrderItemInterface $entity)
    {
        $request = $this->objectFactory->create(['qty' => $entity->getQty()]);

        $productType = $entity->getProductType();
        if (isset($this->processorPool[$productType])) {
            $requestUpdate = $this->processorPool[$productType]->convertToBuyRequest($entity);
            $request->addData($requestUpdate->getData());
        }

        if (isset($this->processorPool['custom_options'])) {
            $requestUpdate = $this->processorPool['custom_options']->convertToBuyRequest($entity);
            $request->addData($requestUpdate->getData());
        }

        return $request;
    }
}
