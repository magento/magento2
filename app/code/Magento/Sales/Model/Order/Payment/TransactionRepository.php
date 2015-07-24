<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\EntityStorage;
use Magento\Sales\Model\Resource\Metadata;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Model\Resource\Order\Payment\Transaction as TransactionResource;

/**
 * Repository class for \Magento\Sales\Model\Order\Payment\Transaction
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Collection Result Factory
     *
     * @var SearchResultFactory
     */
    private $searchResultFactory = null;

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
     * @var Metadata
     */
    private $metaData;

    /**
     * @var SortOrder
     */
    private $sortOrder;

    /**
     * @var EntityStorage
     */
    private $entityStorage;

    /**
     * Repository constructor
     *
     * @param SearchResultFactory $searchResultFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrder $sortOrder,
        Metadata $metaData,
        \Magento\Sales\Model\EntityStorageFactory $entityStorageFactory
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrder = $sortOrder;
        $this->metaData = $metaData;
        $this->entityStorage = $entityStorageFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\TransactionInterface $entity */
            $entity = $this->metaData->getMapper()->load($this->metaData->getNewInstance(), $id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException('Requested entity doesn\'t exist');
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getByTxnType($txnType, $paymentId)
    {
        if (!$txnType) {
            throw new \Magento\Framework\Exception\InputException(__('Txn Id required'));
        }
        if (!$paymentId) {
            throw new \Magento\Framework\Exception\InputException(__('Payment Id required'));
        }
        $identityFieldsForCache = [$txnType, $paymentId];
        $cacheStorage = 'txn_type';
        $entity = $this->entityStorage->getByIdentifyingFields($identityFieldsForCache, $cacheStorage);
        if (!$entity) {
            $filters[] = $this->filterBuilder
                ->setField(TransactionInterface::TXN_TYPE)
                ->setValue($txnType)
                ->create();
            $filters[] = $this->filterBuilder
                ->setField(TransactionInterface::PAYMENT_ID)
                ->setValue($paymentId)
                ->create();
            $sortOrders[] = $this->sortOrder->setField('created_at')->setDirection(Collection::SORT_ORDER_DESC);
            $sortOrders[] = $this->sortOrder->setField('transaction_id')->setDirection(Collection::SORT_ORDER_DESC);
            $entity = current(
                $this->getList(
                    $this->searchCriteriaBuilder->addFilters($filters)->setSortOrders($sortOrders)->create()
                )->getItems()
            );
            if ($entity) {
                $this->entityStorage->addByIdentifyingFields($entity, $identityFieldsForCache, $cacheStorage);
            }
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getByTxnId($txnId, $paymentId)
    {
        if (!$txnId) {
            throw new \Magento\Framework\Exception\InputException(__('Txn Id required'));
        }
        if (!$paymentId) {
            throw new \Magento\Framework\Exception\InputException(__('Payment Id required'));
        }
        $filters[] = $this->filterBuilder
            ->setField(TransactionInterface::TXN_ID)
            ->setValue($txnId)
            ->create();
        $filters[] = $this->filterBuilder
            ->setField(TransactionInterface::PAYMENT_ID)
            ->setValue($paymentId)
            ->create();

        return current($this->getList($this->searchCriteriaBuilder->addFilters($filters)->create())->getItems());
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria)
    {
        /** @var TransactionResource\Collection $collection */
        $collection = $this->searchResultFactory->create();
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
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Sales\Api\Data\TransactionInterface $entity)
    {
        $this->metaData->getMapper()->delete($entity);
        unset($this->registry[$entity->getTransactionId()]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Sales\Api\Data\TransactionInterface $entity)
    {
        $this->metaData->getMapper()->save($entity);
        $this->registry[$entity->getTransactionId()] = $entity;
        return $this->registry[$entity->getTransactionId()];
    }
}
