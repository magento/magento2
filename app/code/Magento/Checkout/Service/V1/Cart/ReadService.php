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
use \Magento\Sales\Model\Quote;
use \Magento\Sales\Model\QuoteRepository;
use \Magento\Sales\Model\Resource\Quote\Collection as QuoteCollection;

use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Exception\InputException;
use \Magento\Framework\Service\V1\Data\Search\FilterGroup;
use \Magento\Checkout\Service\V1\Data\CartSearchResultsBuilder;

use \Magento\Checkout\Service\V1\Data\Cart;
use \Magento\Checkout\Service\V1\Data\CartBuilder;
use \Magento\Checkout\Service\V1\Data\CartMapper;
use \Magento\Checkout\Service\V1\Data\Cart\Totals;
use \Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\TotalsMapper;
use \Magento\Checkout\Service\V1\Data\Cart\Customer;
use \Magento\Checkout\Service\V1\Data\Cart\CustomerBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\CustomerMapper;
use \Magento\Checkout\Service\V1\Data\Cart\Currency;
use \Magento\Checkout\Service\V1\Data\Cart\CurrencyBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\CurrencyMapper;
use \Magento\Checkout\Service\V1\Data\Cart\Totals\Item as ItemTotals;
use \Magento\Checkout\Service\V1\Data\Cart\Totals\ItemBuilder as ItemTotalsBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\Totals\ItemMapper as ItemTotalsMapper;

class ReadService implements ReadServiceInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * @var CartSearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * @var CartBuilder
     */
    private $cartBuilder;

    /**
     * @var CartMapper
     */
    private $cartMapper;

    /**
     * @var CustomerBuilder
     */
    private $customerBuilder;

    /**
     * @var CustomerMapper
     */
    private $customerMapper;

    /**
     * @var TotalsBuilder
     */
    private $totalsBuilder;

    /**
     * @var TotalsMapper
     */
    private $totalsMapper;

    /**
     * @var CurrencyBuilder;
     */
    private $currencyBuilder;

    /**
     * @var CurrencyMapper;
     */
    private $currencyMapper;

    /**
     * @var ItemTotalsBuilder;
     */
    private $itemTotalsBuilder;

    /**
     * @var ItemTotalsMapper;
     */
    private $itemTotalsMapper;

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
     * @param QuoteRepository $quoteRepository
     * @param QuoteCollection $quoteCollection
     * @param CartSearchResultsBuilder $searchResultsBuilder
     * @param CartBuilder $cartBuilder
     * @param CartMapper $cartMapper
     * @param TotalsBuilder $totalsBuilder
     * @param TotalsMapper $totalsMapper
     * @param CustomerBuilder $customerBuilder
     * @param CustomerMapper $customerMapper
     * @param CurrencyBuilder $currencyBuilder
     * @param CurrencyMapper $currencyMapper
     * @param ItemTotalsBuilder $itemTotalsBuilder
     * @param ItemTotalsMapper $itemTotalsMapper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteCollection $quoteCollection,
        CartSearchResultsBuilder $searchResultsBuilder,
        CartBuilder $cartBuilder,
        CartMapper $cartMapper,
        TotalsBuilder $totalsBuilder,
        TotalsMapper $totalsMapper,
        CustomerBuilder $customerBuilder,
        CustomerMapper $customerMapper,
        CurrencyBuilder $currencyBuilder,
        CurrencyMapper $currencyMapper,
        ItemTotalsBuilder $itemTotalsBuilder,
        ItemTotalsMapper $itemTotalsMapper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteCollection = $quoteCollection;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->cartBuilder = $cartBuilder;
        $this->cartMapper = $cartMapper;
        $this->totalsBuilder = $totalsBuilder;
        $this->totalsMapper = $totalsMapper;
        $this->customerBuilder = $customerBuilder;
        $this->customerMapper = $customerMapper;
        $this->currencyBuilder = $currencyBuilder;
        $this->currencyMapper = $currencyMapper;
        $this->itemTotalsBuilder = $itemTotalsBuilder;
        $this->itemTotalsMapper = $itemTotalsMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart($cartId)
    {
        $quote = $this->quoteRepository->get($cartId);
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
        $this->cartBuilder->populateWithArray($this->cartMapper->map($quote));
        $this->customerBuilder->populateWithArray($this->customerMapper->map($quote));
        $this->totalsBuilder->populateWithArray($this->totalsMapper->map($quote));
        $this->totalsBuilder->setItems($this->fetchItemTotalsData($quote));

        $this->cartBuilder->setCustomer($this->customerBuilder->create());
        $this->cartBuilder->setTotals($this->totalsBuilder->create());
        $this->cartBuilder->setCurrency($this->currencyMapper->extractDto($quote));
        return $this->cartBuilder->create();
    }

    /**
     * Fetch quote item totals data
     *
     * @param Quote $quote
     * @return array
     */
    protected function fetchItemTotalsData(Quote $quote)
    {
        $items = [];

        foreach ($quote->getAllItems() as $item) {
            $items[] = $this->itemTotalsMapper->extractDto($item);
        }
        return $items;
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

    /**
     * {@inheritdoc}
     */
    public function getTotals($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $this->totalsBuilder->populateWithArray($this->totalsMapper->map($quote));
        $this->totalsBuilder->setItems($this->fetchItemTotalsData($quote));

        return $this->totalsBuilder->create();
    }
}
