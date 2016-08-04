<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data;
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
     * @var PaymentTokenResourceModel
     */
    protected $resourceModel;

    /**
     * @var PaymentTokenFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /** @var  CollectionProcessorInterface */
    private $collectionProcessor;

    /**
     * @param \Magento\Vault\Model\ResourceModel\PaymentToken $resourceModel
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface | null $collectionProcessor
     */
    public function __construct(
        PaymentTokenResourceModel $resourceModel,
        PaymentTokenFactory $paymentTokenFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resourceModel = $resourceModel;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface Payment token search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \Magento\Vault\Model\ResourceModel\PaymentToken\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        /** @var \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Loads a specified payment token.
     *
     * @param int $entityId The payment token entity ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
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
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken The invoice.
     * @return bool
     */
    public function delete(Data\PaymentTokenInterface $paymentToken)
    {
        /** @var PaymentToken $tokenModel */
        $tokenModel = $this->getById($paymentToken->getEntityId());
        if (empty($tokenModel->getPublicHash())) {
            return false;
        }

        $tokenModel->setIsActive(false);
        $tokenModel->save();

        return true;
    }

    /**
     * Performs persist operations for a specified payment token.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $entity The payment token.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Saved payment token data.
     */
    public function save(Data\PaymentTokenInterface $paymentToken)
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
     * @deprecated
     * @throws \Magento\Framework\Exception\InputException
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
     * @deprecated
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
