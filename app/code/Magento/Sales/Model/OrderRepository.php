<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Sales\Model\ResourceModel\Order as Resource;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\Order\ShippingAssignmentBuilder;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Repository class for @see OrderInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderRepository implements \Magento\Sales\Api\OrderRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var OrderExtension
     */
    private $orderExtension;

    /**
     * @var ShippingAssignmentBuilder
     */
    private $shippingAssignmentBuilder;

    /**
     * OrderInterface[]
     *
     * @var array
     */
    protected $registry = [];

    /**
     * OrderRepository constructor.
     *
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory
    ) {
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var OrderInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->setShippingAssignments($entity);
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return OrderInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        //@TODO: fix search logic
        /** @var \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $searchResult->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResult->setCurPage($searchCriteria->getCurrentPage());
        $searchResult->setPageSize($searchCriteria->getPageSize());
        foreach ($searchResult->getItems() as $order) {
            $this->setShippingAssignments($order);
        }
        return $searchResult;
    }

    /**
     * Register entity to delete
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderInterface $entity)
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
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function save(\Magento\Sales\Api\Data\OrderInterface $entity)
    {
        $this->metadata->getMapper()->save($entity);
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    private function setShippingAssignments(OrderInterface $order)
    {
        /** @var OrderExtensionInterface $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->getOrderExtensionDependency();
        } elseif ($extensionAttributes->getShippingAssignments() !== null) {
            return;
        }
        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignments = $this->getShippingAssignmentBuilderDependency();
        $shippingAssignments->setOrderId($order->getEntityId());
        $extensionAttributes->setShippingAssignments($shippingAssignments->create());
        $order->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get the new OrderExtension dependency for application code
     * @return OrderExtension
     * @deprecated
     */
    private function getOrderExtensionDependency()
    {
        if (!$this->orderExtension instanceof OrderExtension) {
            $this->orderExtension = \Magento\Framework\App\ObjectManager::getInstance()->get(
                '\Magento\Sales\Api\Data\OrderExtension'
            );
        }
        return $this->orderExtension;
    }

    /**
     * Get the new ShippingAssignmentBuilder dependency for application code
     *
     * @return ShippingAssignmentBuilder
     * @deprecated
     */
    private function getShippingAssignmentBuilderDependency()
    {
        if (!$this->shippingAssignmentBuilder instanceof ShippingAssignmentBuilder) {
            $this->shippingAssignmentBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get(
                '\Magento\Sales\Model\Order\ShippingAssignmentBuilder'
            );
        }
        return $this->shippingAssignmentBuilder;
    }
}
