<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data;

use Magento\Checkout\Service\V1\Data\Cart;
use Magento\Sales\Model\Quote;

/**
 * Cart mapper
 */
class CartMapper
{
    /**
     * @var Cart\TotalsBuilder
     */
    private $totalsBuilder;

    /**
     * @var CartBuilder
     */
    private $cartBuilder;

    /**
     * @var Cart\CustomerBuilder
     */
    private $customerBuilder;

    /**
     * @var Cart\CustomerMapper
     */
    private $customerMapper;

    /**
     * @var Cart\TotalsMapper
     */
    private $totalsMapper;

    /**
     * @var Cart\CurrencyMapper;
     */
    private $currencyMapper;

    /**
     * @var Cart\Totals\ItemMapper
     */
    private $itemTotalsMapper;

    /**
     * @param Cart\TotalsBuilder $totalsBuilder
     * @param CartBuilder $cartBuilder
     * @param Cart\CustomerBuilder $customerBuilder
     * @param Cart\CustomerMapper $customerMapper
     * @param Cart\TotalsMapper $totalsMapper
     * @param Cart\CurrencyMapper $currencyMapper
     * @param Cart\Totals\ItemMapper $itemTotalsMapper
     */
    public function __construct(
        Cart\TotalsBuilder $totalsBuilder,
        CartBuilder $cartBuilder,
        Cart\CustomerBuilder $customerBuilder,
        Cart\CustomerMapper $customerMapper,
        Cart\TotalsMapper $totalsMapper,
        Cart\CurrencyMapper $currencyMapper,
        Cart\Totals\ItemMapper $itemTotalsMapper
    ) {
        $this->totalsBuilder = $totalsBuilder;
        $this->cartBuilder = $cartBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->customerMapper = $customerMapper;
        $this->totalsMapper = $totalsMapper;
        $this->currencyMapper = $currencyMapper;
        $this->itemTotalsMapper = $itemTotalsMapper;
    }

    /**
     * Fetch base quote data and map it to DTO fields
     *
     * @param Quote $quote
     * @return array
     */
    public function map(Quote $quote)
    {
        $this->cartBuilder->populateWithArray([
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
        ]);

        $this->customerBuilder->populateWithArray($this->customerMapper->map($quote));
        $this->totalsBuilder->populateWithArray($this->totalsMapper->map($quote));
        $items = [];
        foreach ($quote->getAllItems() as $item) {
            $items[] = $this->itemTotalsMapper->extractDto($item);
        }
        $this->totalsBuilder->setItems($items);

        $this->cartBuilder->setCustomer($this->customerBuilder->create());
        $this->cartBuilder->setTotals($this->totalsBuilder->create());
        $this->cartBuilder->setCurrency($this->currencyMapper->extractDto($quote));
        return $this->cartBuilder->create();
    }
}
