<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

use Magento\Checkout\Service\V1\Data;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Model\Quote;
use Magento\Sales\Model\QuoteRepository;
use Magento\Sales\Model\Resource\Quote\Collection as QuoteCollection;

/**
 * Cart read service object.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * Quote collection.
     *
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * Search results builder.
     *
     * @var Data\CartSearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * Cart mapper.
     *
     * @var Data\CartMapper
     */
    private $cartMapper;

    /**
     * Array of valid search fields.
     *
     * @var array
     */
    private $validSearchFields = [
        'id', 'store_id', 'created_at', 'updated_at', 'converted_at', 'is_active', 'is_virtual',
        'items_count', 'items_qty', 'checkout_method', 'reserved_order_id', 'orig_order_id', 'base_grand_total',
        'grand_total', 'base_subtotal', 'subtotal', 'base_subtotal_with_discount', 'subtotal_with_discount',
        'customer_is_guest', 'customer_id', 'customer_group_id', 'customer_id', 'customer_tax_class_id',
        'customer_email', 'global_currency_code', 'base_currency_code', 'store_currency_code', 'quote_currency_code',
        'store_to_base_rate', 'store_to_quote_rate', 'base_to_global_rate', 'base_to_quote_rate',
    ];

    /**
     * Cart data object - quote field map.
     *
     * @var array
     */
    private $searchFieldMap = [
        'id' => 'entity_id',
    ];

    /**
     * Constructs a cart read service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param QuoteCollection $quoteCollection Quote collection.
     * @param Data\CartSearchResultsBuilder $searchResultsBuilder Search results builder.
     * @param Data\CartMapper $cartMapper Cart mapper.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteCollection $quoteCollection,
        Data\CartSearchResultsBuilder $searchResultsBuilder,
        Data\CartMapper $cartMapper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteCollection = $quoteCollection;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->cartMapper = $cartMapper;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getCart($cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        return $this->cartMapper->map($quote);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     */
    public function getCartForCustomer($customerId)
    {
        $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        return $this->cartMapper->map($quote);
    }

    /**
     * {@inheritDoc}
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Checkout\Service\V1\Data\CartSearchResults Cart search results object.
     */
    public function getCartList(SearchCriteria $searchCriteria)
    {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);

        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $this->quoteCollection);
        }

        $this->searchResultsBuilder->setTotalCount($this->quoteCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $this->quoteCollection->addOrder(
                    $this->getQuoteSearchField($sortOrder->getField()),
                    $sortOrder->getDirection() == SearchCriteria::SORT_ASC ? 'ASC' : 'DESC'
                );
            }
        }
        $this->quoteCollection->setCurPage($searchCriteria->getCurrentPage());
        $this->quoteCollection->setPageSize($searchCriteria->getPageSize());

        $cartList = [];
        /** @var Quote $quote */
        foreach ($this->quoteCollection as $quote) {
            $cartList[] = $this->cartMapper->map($quote);
        }
        $this->searchResultsBuilder->setItems($cartList);

        return $this->searchResultsBuilder->create();
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
            $fields[] = $this->getQuoteSearchField($filter->getField());
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Returns a mapped search field.
     *
     * @param string $field The field.
     * @return string Mapped search field.
     * @throws InputException The specified field cannot be used for search.
     */
    protected function getQuoteSearchField($field)
    {
        if (!in_array($field, $this->validSearchFields)) {
            throw new InputException("Field '{$field}' cannot be used for search.");
        }
        return isset($this->searchFieldMap[$field]) ? $this->searchFieldMap[$field] : $field;
    }
}
