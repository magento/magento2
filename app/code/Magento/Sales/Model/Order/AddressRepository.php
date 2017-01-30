<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderAddressSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Repository class for @see \Magento\Sales\Api\Data\OrderAddressInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements \Magento\Sales\Api\OrderAddressRepositoryInterface
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
     * @var \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    protected $registry = [];

    /**
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
     * Loads a specified order address.
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }

        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\OrderAddressInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }

            $this->registry[$id] = $entity;
        }

        return $this->registry[$id];
    }

    /**
     * Find order addresses by criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        //@TODO: fix search logic
        /** @var \Magento\Sales\Api\Data\OrderAddressSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $searchResult->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResult->setCurPage($searchCriteria->getCurrentPage());
        $searchResult->setPageSize($searchCriteria->getPageSize());

        return $searchResult;
    }

    /**
     * Deletes a specified order address.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Magento\Sales\Api\Data\OrderAddressInterface $entity)
    {
        try {
            $this->metadata->getMapper()->delete($entity);

            unset($this->registry[$entity->getEntityId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete order address'), $e);
        }

        return true;
    }

    /**
     * Deletes order address by Id.
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
     * Performs persist operations for a specified order address.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     * @throws CouldNotSaveException
     */
    public function save(\Magento\Sales\Api\Data\OrderAddressInterface $entity)
    {
        try {
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save order address'), $e);
        }

        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Creates new order address instance.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }
}
