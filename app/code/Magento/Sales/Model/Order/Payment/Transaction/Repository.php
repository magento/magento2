<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\EntityStorage;
use Magento\Sales\Model\EntityStorageFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction as TransactionResource;

/**
 * Repository class for \Magento\Sales\Model\Order\Payment\Transaction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Repository implements TransactionRepositoryInterface
{
    /**
     * Collection Result Factory
     *
     * @var SearchResultFactory
     * @since 2.0.0
     */
    private $searchResultFactory = null;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     * @since 2.0.0
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var Metadata
     * @since 2.0.0
     */
    private $metaData;

    /**
     * @var SortOrderBuilder
     * @since 2.0.0
     */
    private $sortOrderBuilder;

    /**
     * @var EntityStorage
     * @since 2.0.0
     */
    private $entityStorage;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     * @since 2.2.0
     */
    private $collectionProcessor;

    /**
     * Repository constructor.
     * @param SearchResultFactory $searchResultFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Metadata $metaData
     * @param EntityStorageFactory $entityStorageFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @since 2.0.0
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Metadata $metaData,
        EntityStorageFactory $entityStorageFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->metaData = $metaData;
        $this->entityStorage = $entityStorageFactory->create();
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!$this->entityStorage->has($id)) {
            $entity = $this->metaData->getNewInstance()->load($id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->entityStorage->add($entity);
        }
        return $this->entityStorage->get($id);
    }

    /**
     * @param int $transactionType
     * @param int $paymentId
     * @param int $orderId
     * @return bool|\Magento\Framework\Model\AbstractModel|mixed
     * @throws \Magento\Framework\Exception\InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getByTransactionType($transactionType, $paymentId, $orderId)
    {
        $identityFieldsForCache = [$transactionType, $paymentId];
        $cacheStorage = 'txn_type';
        $entity = $this->entityStorage->getByIdentifyingFields($identityFieldsForCache, $cacheStorage);
        if (!$entity) {
            $typeFilter = $this->filterBuilder
                ->setField(TransactionInterface::TXN_TYPE)
                ->setValue($transactionType)
                ->create();
            $idFilter = $this->filterBuilder
                ->setField(TransactionInterface::PAYMENT_ID)
                ->setValue($paymentId)
                ->create();
            $transactionIdSort = $this->sortOrderBuilder
                ->setField('transaction_id')
                ->setDirection(Collection::SORT_ORDER_DESC)
                ->create();
            $createdAtSort = $this->sortOrderBuilder
                ->setField('created_at')
                ->setDirection(Collection::SORT_ORDER_DESC)
                ->create();
            $entity = current(
                $this->getList(
                    $this->searchCriteriaBuilder
                        ->addFilters([$typeFilter])
                        ->addFilters([$idFilter])
                        ->addSortOrder($transactionIdSort)
                        ->addSortOrder($createdAtSort)
                        ->create()
                )->getItems()
            );
            if ($entity) {
                $this->entityStorage->addByIdentifyingFields($entity, $identityFieldsForCache, $cacheStorage);
            }
        }

        return $entity;
    }

    /**
     * @param int $transactionId
     * @param int $paymentId
     * @param int $orderId
     * @return bool|\Magento\Framework\Api\ExtensibleDataInterface|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
     */
    public function getByTransactionId($transactionId, $paymentId, $orderId)
    {
        $identityFieldsForCache = [$transactionId, $paymentId, $orderId];
        $cacheStorage = 'txn_id';
        $entity = $this->entityStorage->getByIdentifyingFields($identityFieldsForCache, $cacheStorage);
        if (!$entity) {
            $entity = $this->metaData->getNewInstance();
            $this->metaData->getMapper()->loadObjectByTxnId(
                $entity,
                $orderId,
                $paymentId,
                $transactionId
            );
            if ($entity && $entity->getId()) {
                $this->entityStorage->addByIdentifyingFields($entity, $identityFieldsForCache, $cacheStorage);
                return $entity;
            }
            return false;
        }
        return $entity;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var TransactionResource\Collection $collection */
        $collection = $this->searchResultFactory->create();
        $collection->setSearchCriteria($searchCriteria);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->addPaymentInformation(['method']);
        $collection->addOrderInformation(['increment_id']);
        return $collection;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\TransactionInterface $entity)
    {
        $this->metaData->getMapper()->delete($entity);
        $this->entityStorage->remove($entity->getTransactionId());
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\TransactionInterface $entity)
    {
        $this->metaData->getMapper()->save($entity);
        $this->entityStorage->add($entity);
        return $this->entityStorage->get($entity->getTransactionId());
    }

    /**
     * Creates new Transaction instance.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface Transaction interface.
     * @since 2.0.0
     */
    public function create()
    {
        return $this->metaData->getNewInstance();
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 2.2.0
     * @return CollectionProcessorInterface
     * @since 2.2.0
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
