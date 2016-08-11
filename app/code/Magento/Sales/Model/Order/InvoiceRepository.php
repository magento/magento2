<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceSearchResultInterfaceFactory as SearchResultFactory;

/**
 * Class InvoiceRepository
 */
class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * \Magento\Sales\Api\Data\InvoiceInterface[]
     *
     * @var array
     */
    protected $registry = [];

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * Repository constructor
     *
     * @param Metadata $invoiceMetadata
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        Metadata $invoiceMetadata,
        SearchResultFactory $searchResultFactory
    ) {
        $this->metadata = $invoiceMetadata;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * Load entity
     *
     * @param int $id
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\InvoiceInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Sales\Api\Data\InvoiceInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $collection */
        $collection = $this->searchResultFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $collection->setSearchCriteria($searchCriteria);
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        return $collection;
    }

    /**
     * Register entity to delete
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\InvoiceInterface $entity)
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
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    public function save(\Magento\Sales\Api\Data\InvoiceInterface $entity)
    {
        $this->metadata->getMapper()->save($entity);
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }
}
