<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Service\V1\Cart;

use \Magento\Framework\Service\V1\Data\SearchCriteria;
use \Magento\Sales\Model\QuoteFactory;
use \Magento\Sales\Model\Quote;
use \Magento\Sales\Model\Resource\Quote\Collection as QuoteCollection;

use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Exception\InputException;
use \Magento\Framework\Service\V1\Data\Search\FilterGroup;
use \Magento\Checkout\Service\V1\Data\CartBuilder;
use \Magento\Checkout\Service\V1\Data\CartSearchResultsBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\CustomerBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\CurrencyBuilder;
use \Magento\Checkout\Service\V1\Data\Cart;
use \Magento\Checkout\Service\V1\Data\Cart\Totals;
use \Magento\Checkout\Service\V1\Data\Cart\Customer;
use \Magento\Checkout\Service\V1\Data\Cart\Currency;

class ReadService implements ReadServiceInterface
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CartBuilder
     */
    private $cartBuilder;

    /**
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * @var CartSearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * @var CustomerBuilder
     */
    private $customerBuilder;

    /**
     * @var TotalsBuilder
     */
    private $totalsBuilder;

    /**
     * @var CurrencyBuilder;
     */
    private $currencyBuilder;

    /**
     * @var array
     */
    private $validSearchFields = array(
        'id', 'store_id', 'created_at', 'updated_at', 'converted_at', 'is_active', 'is_virtual',
        'items_count', 'items_qty', 'checkout_method', 'reserved_order_id', 'orig_order_id', 'base_grand_total',
        'grand_total', 'base_subtotal', 'subtotal', 'base_subtotal_with_discount', 'subtotal_with_discount',
        'customer_is_guest', 'customer_id', 'customer_group_id', 'customer_id', 'customer_tax_class_id',
        'customer_email', 'global_currency_code', 'base_currency_code', 'store_currency_code', 'quote_currency_code',
        'store_to_base_rate', 'store_to_quote_rate', 'base_to_global_rate', 'base_to_quote_rate',
    );

    /**
     * Cart data object - quote field map
     *
     * @var array
     */
    private $searchFieldMap = array(
        'id' => 'entity_id',
    );

    /**
     * @param QuoteFactory $quoteFactory
     * @param QuoteCollection $quoteCollection
     * @param CartBuilder $cartBuilder
     * @param CartSearchResultsBuilder $searchResultsBuilder
     * @param TotalsBuilder $totalsBuilder
     * @param CustomerBuilder $customerBuilder
     * @param CurrencyBuilder $currencyBuilder
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteCollection $quoteCollection,
        CartBuilder $cartBuilder,
        CartSearchResultsBuilder $searchResultsBuilder,
        TotalsBuilder $totalsBuilder,
        CustomerBuilder $customerBuilder,
        CurrencyBuilder $currencyBuilder
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteCollection = $quoteCollection;
        $this->cartBuilder = $cartBuilder;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->totalsBuilder = $totalsBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->currencyBuilder = $currencyBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart($cartId)
    {
        $quote = $this->quoteFactory->create()->load($cartId);
        if ($quote->getId() != $cartId) {
            throw new NoSuchEntityException('There is no cart with provided ID.');
        }
        return $this->createCartDataObject($quote);
    }

    /**
     * {@inheritdoc}
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
            foreach ($sortOrders as $field => $direction) {
                $this->quoteCollection->addOrder(
                    $this->getQuoteSearchField($field),
                    $direction == SearchCriteria::SORT_ASC ? 'ASC' : 'DESC'
                );
            }
        }
        $this->quoteCollection->setCurPage($searchCriteria->getCurrentPage());
        $this->quoteCollection->setPageSize($searchCriteria->getPageSize());

        $cartList = [];
        /** @var Quote $quote */
        foreach ($this->quoteCollection as $quote) {
            $cartList[] = $this->createCartDataObject($quote);
        }
        $this->searchResultsBuilder->setItems($cartList);

        return $this->searchResultsBuilder->create();
    }

    /**
     * Create cart data object based on given quote
     *
     * @param Quote $quote
     * @return Cart
     */
    protected function createCartDataObject(Quote $quote)
    {
        $this->cartBuilder->populateWithArray(array(
            Cart::ID => $quote->getId(),
            Cart::STORE_ID  => $quote->getStoreId(),
            Cart::CREATED_AT  => $quote->getCreatedAt(),
            Cart::UPDATED_AT  => $quote->getUpdatedAt(),
            Cart::CONVERTED_AT => $quote->getConvertedAt(),
            Cart::IS_ACTIVE => $quote->getIsActive(),
            Cart::IS_VIRTUAL => $quote->getIsVirtual(),
            Cart::ITEMS_COUNT => $quote->getItemsCount(),
            Cart::ITEMS_QUANTITY => $quote->getItemsQty(),
            Cart::CHECKOUT_METHOD => $quote->getCheckoutMethod(),
            Cart::RESERVED_ORDER_ID => $quote->getReservedOrderId(),
            Cart::ORIG_ORDER_ID => $quote->getOrigOrderId(),
        ));

        $this->totalsBuilder->populateWithArray(array(
            Totals::BASE_GRAND_TOTAL => $quote->getBaseGrandTotal(),
            Totals::GRAND_TOTAL => $quote->getGrandTotal(),
            Totals::BASE_SUBTOTAL => $quote->getBaseSubtotal(),
            Totals::SUBTOTAL => $quote->getSubtotal(),
            Totals::BASE_SUBTOTAL_WITH_DISCOUNT => $quote->getBaseSubtotalWithDiscount(),
            Totals::SUBTOTAL_WITH_DISCOUNT => $quote->getSubtotalWithDiscount(),
        ));

        $this->customerBuilder->populateWithArray(array(
            Customer::ID => $quote->getCustomerId(),
            Customer::EMAIL => $quote->getCustomerEmail(),
            Customer::GROUP_ID => $quote->getCustomerGroupId(),
            Customer::TAX_CLASS_ID => $quote->getCustomerTaxClassId(),
            Customer::PREFIX => $quote->getCustomerPrefix(),
            Customer::FIRST_NAME => $quote->getCustomerFirstname(),
            Customer::MIDDLE_NAME => $quote->getCustomerMiddlename(),
            Customer::LAST_NAME => $quote->getCustomerLastname(),
            Customer::SUFFIX => $quote->getCustomerSuffix(),
            Customer::DOB => $quote->getCustomerDob(),
            Customer::NOTE => $quote->getCustomerNote(),
            Customer::NOTE_NOTIFY => $quote->getCustomerNoteNotify(),
            Customer::IS_GUEST => $quote->getCustomerIsGuest(),
            Customer::GENDER => $quote->getCustomerGender(),
            Customer::TAXVAT => $quote->getCustomerTaxvat(),
        ));

        $this->currencyBuilder->populateWithArray(array(
            Currency::GLOBAL_CURRENCY_CODE => $quote->getGlobalCurrencyCode(),
            Currency::BASE_CURRENCY_CODE => $quote->getBaseCurrencyCode(),
            Currency::STORE_CURRENCY_CODE => $quote->getStoreCurrencyCode(),
            Currency::QUOTE_CURRENCY_CODE => $quote->getQuoteCurrencyCode(),
            Currency::STORE_TO_BASE_RATE => $quote->getStoreToBaseRate(),
            Currency::STORE_TO_QUOTE_RATE => $quote->getStoreToQuoteRate(),
            Currency::BASE_TO_GLOBAL_RATE => $quote->getBaseToGlobalRate(),
            Currency::BASE_TO_QUOTE_RATE => $quote->getBaseToQuoteRate(),
        ));

        $this->cartBuilder->setCustomer($this->customerBuilder->create());
        $this->cartBuilder->setTotals($this->totalsBuilder->create());
        $this->cartBuilder->setCurrency($this->currencyBuilder->create());
        return $this->cartBuilder->create();
    }

    /**
     * Add FilterGroup to the given quote collection.
     *
     * @param FilterGroup $filterGroup
     * @param QuoteCollection $collection
     * @return void
     * @throws InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, QuoteCollection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $fields[] = $this->getQuoteSearchField($filter->getField());
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = array($condition => $filter->getValue());
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Retrieve mapped search field
     *
     * @param string $field
     * @return string
     * @throws InputException
     */
    protected function getQuoteSearchField($field)
    {
        if (!in_array($field, $this->validSearchFields)) {
            throw new InputException("Field '{$field}' cannot be used for search.");
        }
        return isset($this->searchFieldMap[$field]) ? $this->searchFieldMap[$field] : $field;
    }
}
