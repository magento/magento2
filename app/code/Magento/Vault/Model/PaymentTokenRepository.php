<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Vault\Api\Data;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;
use Magento\Vault\Model\ResourceModel\PaymentToken\Collection;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

/**
 * Vault payment token repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentTokenRepository implements PaymentTokenRepositoryInterface
{
    /**
     * PaymentTokenRepository constructor.
     *
     * @param PaymentTokenResourceModel $resourceModel
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        protected readonly PaymentTokenResourceModel $resourceModel,
        protected readonly PaymentTokenFactory $paymentTokenFactory,
        protected readonly FilterBuilder $filterBuilder,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory,
        protected readonly CollectionFactory $collectionFactory,
        private ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria The search criteria.
     * @return PaymentTokenSearchResultsInterface Payment token search result interface.
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        /** @var PaymentTokenSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Loads a specified payment token.
     *
     * @param int $entityId The payment token entity ID.
     * @return PaymentTokenInterface Payment token interface.
     */
    public function getById($entityId)
    {
        $tokenModel = $this->paymentTokenFactory->create();
        $this->resourceModel->load($tokenModel, $entityId);
        return $tokenModel;
    }

    /**
     * Deletes a specified payment token.
     *
     * @param PaymentTokenInterface $paymentToken The invoice.
     * @return bool
     */
    public function delete(PaymentTokenInterface $paymentToken)
    {
        /** @var PaymentToken $tokenModel */
        $tokenModel = $this->getById($paymentToken->getEntityId());
        if (empty($tokenModel->getPublicHash())) {
            return false;
        }

        $tokenModel->setIsActive(false);
        $tokenModel->setIsVisible(false);
        $tokenModel->save();

        return true;
    }

    /**
     * Performs persist operations for a specified payment token.
     *
     * @param PaymentTokenInterface $paymentToken The payment token.
     * @return PaymentTokenInterface Saved payment token data.
     */
    public function save(PaymentTokenInterface $paymentToken)
    {
        /** @var PaymentToken $paymentToken */
        $this->resourceModel->save($paymentToken);
        return $paymentToken;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @deprecated 101.0.0
     * @throws InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = ObjectManager::getInstance()->get(
                CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
