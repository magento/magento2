<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestSafetyInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory;
use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Repository for quote entity.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepository implements CartRepositoryInterface, ResetAfterRequestInterface
{
    /**
     * @var Quote[]
     */
    protected $quotesById = [];

    /**
     * @var Quote[]
     */
    protected $quotesByCustomerId = [];

    /**
     * @var QuoteFactory
     * @deprecated 101.1.2
     * @see no longer used
     */
    protected $quoteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var QuoteCollection|null
     * @deprecated 101.0.0
     * @see $quoteCollectionFactory
     */
    protected $quoteCollection;

    /**
     * @var CartSearchResultsInterfaceFactory
     */
    protected $searchResultsDataFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var LoadHandler
     */
    private $loadHandler;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var CartInterfaceFactory
     */
    private $cartFactory;

    /**
     * @var RequestSafetyInterface
     */
    private $requestSafety;

    /**
     * Constructor
     *
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param QuoteCollection $quoteCollection Deprecated.  Use $quoteCollectionFactory
     * @param CartSearchResultsInterfaceFactory $searchResultsDataFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param QuoteCollectionFactory|null $quoteCollectionFactory
     * @param CartInterfaceFactory|null $cartFactory
     * @param RequestSafetyInterface|null $requestSafety
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        QuoteCollection $quoteCollection,
        CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ?CollectionProcessorInterface $collectionProcessor = null,
        ?QuoteCollectionFactory $quoteCollectionFactory = null,
        ?CartInterfaceFactory $cartFactory = null,
        ?RequestSafetyInterface $requestSafety = null
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->searchResultsDataFactory = $searchResultsDataFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: ObjectManager::getInstance()
            ->get(CollectionProcessor::class);
        $this->quoteCollectionFactory = $quoteCollectionFactory ?: ObjectManager::getInstance()
            ->get(QuoteCollectionFactory::class);
        $this->cartFactory = $cartFactory ?: ObjectManager::getInstance()->get(CartInterfaceFactory::class);
        $this->requestSafety = $requestSafety ?: ObjectManager::getInstance()->get(RequestSafetyInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->quotesById = [];
        $this->quotesByCustomerId = [];
        $this->quoteCollection = null;
    }

    /**
     * @inheritdoc
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesById[$cartId])) {
            $quote = $this->loadQuote('loadByIdWithoutStore', 'cartId', $cartId, $sharedStoreIds);
            $this->quotesById[$cartId] = $quote;
            $this->getLoadHandler()->load($quote);
        }
        return $this->quotesById[$cartId];
    }

    /**
     * @inheritdoc
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesByCustomerId[$customerId])) {
            $customerQuote = $this->loadQuote('loadByCustomer', 'customerId', $customerId, $sharedStoreIds);
            $customerQuoteId = $customerQuote->getId();
            //prevent loading quote items for same quote
            if (isset($this->quotesById[$customerQuoteId])) {
                $customerQuote = $this->quotesById[$customerQuoteId];
            } else {
                $this->getLoadHandler()->load($customerQuote);
            }
            $this->quotesById[$customerQuoteId] = $customerQuote;
            $this->quotesByCustomerId[$customerId] = $customerQuote;
        }
        return $this->quotesByCustomerId[$customerId];
    }

    /**
     * @inheritdoc
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $this->validateCachedActiveQuote((int)$cartId);
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $quote;
    }

    /**
     * Validates if cached quote is still active.
     *
     * @param int $cartId
     * @return void
     * @throws NoSuchEntityException
     */
    private function validateCachedActiveQuote(int $cartId): void
    {
        if (isset($this->quotesById[$cartId]) && !$this->requestSafety->isSafeMethod()) {
            $quote = $this->cartFactory->create();
            if (is_callable([$quote, 'setSharedStoreIds'])) {
                $quote->setSharedStoreIds(['*']);
            }
            $quote->loadActive($cartId);
            if (!$quote->getIsActive()) {
                throw NoSuchEntityException::singleField('cartId', $cartId);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        $this->validateCachedCustomerActiveQuote((int)$customerId);
        $quote = $this->getForCustomer($customerId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $quote;
    }

    /**
     * Validates if cached customer quote is still active.
     *
     * @param int $customerId
     * @return void
     * @throws NoSuchEntityException
     */
    private function validateCachedCustomerActiveQuote(int $customerId): void
    {
        if (isset($this->quotesByCustomerId[$customerId]) && !$this->requestSafety->isSafeMethod()) {
            $quoteId = $this->quotesByCustomerId[$customerId]->getId();
            $quote = $this->cartFactory->create();
            if (is_callable([$quote, 'setSharedStoreIds'])) {
                $quote->setSharedStoreIds(['*']);
            }
            $quote->loadActive($quoteId);
            if (!$quote->getIsActive()) {
                throw NoSuchEntityException::singleField('customerId', $customerId);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function save(CartInterface $quote)
    {
        if ($quote->getId()) {
            $currentQuote = $this->get($quote->getId(), [$quote->getStoreId()]);

            foreach ($currentQuote->getData() as $key => $value) {
                if (!$quote->hasData($key)) {
                    $quote->setData($key, $value);
                }
            }
        }
        $this->getSaveHandler()->save($quote);
        unset($this->quotesById[$quote->getId()]);
        unset($this->quotesByCustomerId[$quote->getCustomerId()]);
    }

    /**
     * @inheritdoc
     */
    public function delete(CartInterface $quote)
    {
        $quoteId = $quote->getId();
        $customerId = $quote->getCustomerId();
        $quote->delete();
        unset($this->quotesById[$quoteId]);
        unset($this->quotesByCustomerId[$customerId]);
    }

    /**
     * Load quote with different methods
     *
     * @param string $loadMethod
     * @param string $loadField
     * @param int $identifier
     * @param int[] $sharedStoreIds
     * @throws NoSuchEntityException
     * @return CartInterface
     */
    protected function loadQuote($loadMethod, $loadField, $identifier, array $sharedStoreIds = [])
    {
        /** @var CartInterface $quote */
        $quote = $this->cartFactory->create();
        if ($sharedStoreIds && is_callable([$quote, 'setSharedStoreIds'])) {
            $quote->setSharedStoreIds($sharedStoreIds);
        }
        $quote->setStoreId($this->storeManager->getStore()->getId())->$loadMethod($identifier);
        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $this->quoteCollection = $this->quoteCollectionFactory->create();
        /** @var \Magento\Quote\Api\Data\CartSearchResultsInterface $searchData */
        $searchData = $this->searchResultsDataFactory->create();
        $searchData->setSearchCriteria($searchCriteria);

        $this->collectionProcessor->process($searchCriteria, $this->quoteCollection);
        $this->extensionAttributesJoinProcessor->process($this->quoteCollection);
        foreach ($this->quoteCollection->getItems() as $quote) {
            /** @var CartInterface $quote */
            $this->getLoadHandler()->load($quote);
        }
        $searchData->setItems($this->quoteCollection->getItems());
        $searchData->setTotalCount($this->quoteCollection->getSize());
        return $searchData;
    }

    /**
     * Adds a specified filter group to the specified quote collection.
     *
     * @param FilterGroup $filterGroup The filter group.
     * @param QuoteCollection $collection The quote collection.
     * @return void
     * @deprecated 101.0.0
     * @see no longer used
     * @throws InputException The specified filter group or quote collection does not exist.
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, QuoteCollection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $fields[] = $filter->getField();
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Get new SaveHandler dependency for application code.
     *
     * @return SaveHandler
     */
    private function getSaveHandler()
    {
        if (!$this->saveHandler) {
            $this->saveHandler = ObjectManager::getInstance()->get(SaveHandler::class);
        }
        return $this->saveHandler;
    }

    /**
     * Get load handler instance.
     *
     * @return LoadHandler
     */
    private function getLoadHandler()
    {
        if (!$this->loadHandler) {
            $this->loadHandler = ObjectManager::getInstance()->get(LoadHandler::class);
        }
        return $this->loadHandler;
    }
}
