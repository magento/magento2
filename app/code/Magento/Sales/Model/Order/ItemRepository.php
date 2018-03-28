<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Metadata;

/**
 * Repository class for @see OrderItemInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepository implements OrderItemRepositoryInterface
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
     * @var ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var ProductOptionProcessorInterface[]
     */
    protected $processorPool;

    /**
     * @var OrderItemInterface[]
     */
    protected $registry = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * ItemRepository constructor.
     * @param DataObjectFactory $objectFactory
     * @param Metadata $metadata
     * @param OrderItemSearchResultInterfaceFactory $searchResultFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param array $processorPool
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        Metadata $metadata,
        OrderItemSearchResultInterfaceFactory $searchResultFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        array $processorPool = [],
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->objectFactory = $objectFactory;
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->processorPool = $processorPool;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * load entity
     *
     * @param int $id
     * @return OrderItemInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var OrderItemInterface $orderItem */
            $orderItem = $this->metadata->getNewInstance()->load($id);
            if (!$orderItem->getItemId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }

            $this->addProductOption($orderItem);
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
            $this->addProductOption($orderItem);
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
            $request = $this->getBuyRequest($entity);
            $entity->setProductOptions(['info_buyRequest' => $request->toArray()]);
        }

        $this->metadata->getMapper()->save($entity);
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Add product option data
     *
     * @param OrderItemInterface $orderItem
     * @return $this
     */
    protected function addProductOption(OrderItemInterface $orderItem)
    {
        /** @var DataObject $request */
        $request = $orderItem->getBuyRequest();

        $productType = $orderItem->getProductType();
        if (isset($this->processorPool[$productType])
            && !$orderItem->getParentItemId()) {
            $data = $this->processorPool[$productType]->convertToProductOption($request);
            if ($data) {
                $this->setProductOption($orderItem, $data);
            }
        }

        if (isset($this->processorPool['custom_options'])
            && !$orderItem->getParentItemId()) {
            $data = $this->processorPool['custom_options']->convertToProductOption($request);
            if ($data) {
                $this->setProductOption($orderItem, $data);
            }
        }

        return $this;
    }

    /**
     * Set product options data
     *
     * @param OrderItemInterface $orderItem
     * @param array $data
     * @return $this
     */
    protected function setProductOption(OrderItemInterface $orderItem, array $data)
    {
        $productOption = $orderItem->getProductOption();
        if (!$productOption) {
            $productOption = $this->productOptionFactory->create();
            $orderItem->setProductOption($productOption);
        }

        $extensionAttributes = $productOption->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
            $productOption->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setData(key($data), current($data));

        return $this;
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

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.2.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
