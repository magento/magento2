<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Resource\Order\Payment\Transaction as TransactionResource;

/**
 * Repository class for \Magento\Sales\Model\Order\Payment\Transaction
 */
class TransactionRepository
{
    /**
     * transactionFactory
     *
     * @var TransactionFactory
     */
    private $transactionFactory = null;

    /**
     * Collection Factory
     *
     * @var TransactionResource\CollectionFactory
     */
    private $transactionCollectionFactory = null;

    /**
     * Magento\Sales\Model\Order\Payment\Transaction[]
     *
     * @var array
     */
    private $registry = [];

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Repository constructor
     *
     * @param TransactionFactory $transactionFactory
     * @param TransactionResource\CollectionFactory $transactionCollectionFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionResource\CollectionFactory $transactionCollectionFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * load entity
     *
     * @param int $id
     * @return Transaction
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException('ID required');
        }
        if (!isset($this->registry[$id])) {
            $filter = $this->filterBuilder->setField('transaction_id')->setValue($id)->setConditionType('eq')->create();
            $this->searchCriteriaBuilder->addFilter([$filter]);
            $this->find($this->searchCriteriaBuilder->create());

            if (!isset($this->registry[$id])) {
                throw new \Magento\Framework\Exception\NoSuchEntityException('Requested entity doesn\'t exist');
            }
        }
        return $this->registry[$id];
    }

    /**
     * Register entity
     *
     * @param Transaction $object
     * @return TransactionRepository
     */
    public function register(Transaction $object)
    {
        if ($object->getId() && !isset($this->registry[$object->getId()])) {
            $this->registry[$object->getId()] = $object;
        }
        return $this;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteria  $criteria
     * @return Transaction[]
     */
    public function find(\Magento\Framework\Api\SearchCriteria $criteria)
    {
        /** @var TransactionResource\Collection $collection */
        $collection = $this->transactionCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $collection->addPaymentInformation(['method']);
        $collection->addOrderInformation(['increment_id']);
        foreach ($collection as $object) {
            $this->register($object);
        }
        $objectIds = $collection->getAllIds();
        return array_intersect_key($this->registry, array_flip($objectIds));
    }
}
