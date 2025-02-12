<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\ProductOption;
use Magento\Sales\Model\ResourceModel\Metadata;

/**
 * Repository class for @see OrderItemInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepository implements OrderItemRepositoryInterface, ResetAfterRequestInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var OrderItemSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var ProductOptionProcessorInterface[]
     */
    protected $processorPool;

    /**
     * @var OrderItemInterface[]
     */
    protected $registry = [];

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ProductOption
     */
    private $productOption;

    /**
     * @param DataObjectFactory $objectFactory
     * @param Metadata $metadata
     * @param OrderItemSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductOption $productOption
     * @param array $processorPool
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        Metadata $metadata,
        OrderItemSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        ProductOption $productOption,
        array $processorPool = []
    ) {
        $this->objectFactory = $objectFactory;
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->productOption = $productOption;
        $this->processorPool = $processorPool;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->registry = [];
    }

    /**
     * Loads entity.
     *
     * @param int $id
     * @return OrderItemInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('An ID is needed. Set the ID and try again.'));
        }
        if (!isset($this->registry[$id])) {
            /** @var OrderItemInterface $orderItem */
            $orderItem = $this->metadata->getNewInstance()->load($id);
            if (!$orderItem->getItemId()) {
                throw new NoSuchEntityException(
                    __("The entity that was requested doesn't exist. Verify the entity and try again.")
                );
            }

            $this->productOption->add($orderItem);
            $this->addParentItem($orderItem);
            $this->registry[$id] = $orderItem;
        }
        return $this->registry[$id];
    }

    /**
     * Find entities by criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return OrderItemInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        /** @var OrderItemInterface $orderItem */
        foreach ($searchResult->getItems() as $orderItem) {
            $this->productOption->add($orderItem);
        }

        return $searchResult;
    }

    /**
     * Register entity to delete
     *
     * @param OrderItemInterface $entity
     * @return bool
     */
    public function delete(OrderItemInterface $entity)
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
     * @param OrderItemInterface $entity
     * @return OrderItemInterface
     */
    public function save(OrderItemInterface $entity)
    {
        if ($entity->getProductOption()) {
            $entity->setProductOptions($this->getItemProductOptions($entity));
        }

        $this->metadata->getMapper()->save($entity);
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Return product options
     *
     * @param OrderItemInterface $entity
     * @return array
     */
    private function getItemProductOptions(OrderItemInterface $entity): array
    {
        $request = $this->getBuyRequest($entity);
        $productOptions = $entity->getProductOptions();
        $productOptions['info_buyRequest'] = $productOptions && !empty($productOptions['info_buyRequest'])
            ? array_merge($productOptions['info_buyRequest'], $request->toArray())
            : $request->toArray();

        return $productOptions;
    }

    /**
     * Set parent item.
     *
     * @param OrderItemInterface $orderItem
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function addParentItem(OrderItemInterface $orderItem)
    {
        if ($parentId = $orderItem->getParentItemId()) {
            $orderItem->setParentItem($this->get($parentId));
        } else {
            $orderCollection = $orderItem->getOrder()->getItemsCollection()->filterByParent($orderItem->getItemId());

            foreach ($orderCollection->getItems() as $item) {
                if ($item->getParentItemId() === $orderItem->getItemId()) {
                    $item->setParentItem($orderItem);
                }
            }
        }
    }

    /**
     * Retrieve order item's buy request
     *
     * @param OrderItemInterface $entity
     * @return DataObject
     */
    protected function getBuyRequest(OrderItemInterface $entity)
    {
        $request = $this->objectFactory->create(['qty' => $entity->getQtyOrdered()]);

        $productType = $entity->getProductType();
        if (isset($this->processorPool[$productType])
            && !$entity->getParentItemId()) {
            $productOption = $entity->getProductOption();
            if ($productOption) {
                $requestUpdate = $this->processorPool[$productType]->convertToBuyRequest($productOption);
                $request->addData($requestUpdate->getData());
            }
        }

        if (isset($this->processorPool['custom_options'])
            && !$entity->getParentItemId()) {
            $productOption = $entity->getProductOption();
            if ($productOption) {
                $requestUpdate = $this->processorPool['custom_options']->convertToBuyRequest($productOption);
                $request->addData($requestUpdate->getData());
            }
        }

        return $request;
    }
}
