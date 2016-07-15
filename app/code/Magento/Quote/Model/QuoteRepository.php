<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Model\QuoteRepository\LoadHandler;

/**
 * Class QuoteRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepository implements \Magento\Quote\Api\CartRepositoryInterface
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
     */
    protected $quoteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected $quoteCollection;

    /**
     * @var \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory
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
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection
     * @param \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection,
        \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->searchResultsDataFactory = $searchResultsDataFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesById[$cartId])) {
            $quote = $this->loadQuote('load', 'cartId', $cartId, $sharedStoreIds);
            $this->getLoadHandler()->load($quote);
            $this->quotesById[$cartId] = $quote;
        }
        return $this->quotesById[$cartId];
    }

    /**
     * {@inheritdoc}
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesByCustomerId[$customerId])) {
            $quote = $this->loadQuote('loadByCustomer', 'customerId', $customerId, $sharedStoreIds);
            $this->getLoadHandler()->load($quote);
            $this->quotesById[$quote->getId()] = $quote;
            $this->quotesByCustomerId[$customerId] = $quote;
        }
        return $this->quotesByCustomerId[$customerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        $quote = $this->getForCustomer($customerId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Quote\Api\Data\CartInterface $quote)
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
     * {@inheritdoc}
     */
    public function delete(\Magento\Quote\Api\Data\CartInterface $quote)
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
     * @return Quote
     */
    protected function loadQuote($loadMethod, $loadField, $identifier, array $sharedStoreIds = [])
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        if ($sharedStoreIds) {
            $quote->setSharedStoreIds($sharedStoreIds);
        }
        $quote->setStoreId($this->storeManager->getStore()->getId())->$loadMethod($identifier);
        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }

    /**
     * Get quote collection
     * Temporary method to support release backward compatibility.
     *
     * @deprecated
     * @return QuoteCollection
     */
    protected function getQuoteCollection()
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory */
        $collectionFactory = ObjectManager::getInstance()->get(QuoteCollectionFactory::class);
        return $collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $this->quoteCollection = $this->getQuoteCollection();
        /** @var \Magento\Quote\Api\Data\CartSearchResultsInterface $searchData */
        $searchData = $this->searchResultsDataFactory->create();
        $searchData->setSearchCriteria($searchCriteria);

        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $this->quoteCollection);
        }

        $searchData->setTotalCount($this->quoteCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $this->quoteCollection->addOrder(
                    $sortOrder->getField(),
                    $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'ASC' : 'DESC'
                );
            }
        }
        $this->quoteCollection->setCurPage($searchCriteria->getCurrentPage());
        $this->quoteCollection->setPageSize($searchCriteria->getPageSize());
        $this->extensionAttributesJoinProcessor->process($this->quoteCollection);
        foreach ($this->quoteCollection->getItems() as $quote) {
            /** @var CartInterface $quote */
            $this->getLoadHandler()->load($quote);
        }
        $searchData->setItems($this->quoteCollection->getItems());

        return $searchData;
    }

    /**
     * Adds a specified filter group to the specified quote collection.
     *
     * @param FilterGroup $filterGroup The filter group.
     * @param QuoteCollection $collection The quote collection.
     * @return void
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
     * @return SaveHandler
     * @deprecated
     */
    private function getSaveHandler()
    {
        if (!$this->saveHandler) {
            $this->saveHandler = ObjectManager::getInstance()->get(SaveHandler::class);
        }
        return $this->saveHandler;
    }

    /**
     * @return LoadHandler
     * @deprecated
     */
    private function getLoadHandler()
    {
        if (!$this->loadHandler) {
            $this->loadHandler = ObjectManager::getInstance()->get(LoadHandler::class);
        }
        return $this->loadHandler;
    }
}
