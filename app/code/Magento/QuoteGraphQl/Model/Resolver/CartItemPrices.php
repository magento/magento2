<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Cart\Totals;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @inheritdoc
 */
class CartItemPrices implements ResolverInterface
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var Totals
     */
    private $totals;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var PriceCurrencyObj
     */
    private $priceCurrencyObj;


    /**
     * @param TotalsCollector $totalsCollector
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrencyObj
     */
    public function __construct(
        TotalsCollector $totalsCollector,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrencyObj
    ) {
        $this->totalsCollector = $totalsCollector;
        $this->storeManager = $storeManager;
        $this->priceCurrencyObj = $priceCurrencyObj;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        if (!$this->totals) {
            // The totals calculation is based on quote address.
            // But the totals should be calculated even if no address is set
            $this->totals = $this->totalsCollector->collectQuoteTotals($cartItem->getQuote());
        }
        // update currency code
        //$currencyCode = $cartItem->getQuote()->getQuoteCurrencyCode();
        $currencyCode = $this->getCurrentCurrency();
        // update currency price
        $cartPrice = $this->setConvertPrice($cartItem->getPrice());

        return [
            'price' => [
                'currency' => $currencyCode,
                'value' => $cartPrice,
            ],
            'row_total' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getRowTotal(),
            ],
            'row_total_including_tax' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getRowTotalInclTax(),
            ],
            'total_item_discount' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getDiscountAmount(),
            ],
            'discounts' => $this->getDiscountValues($cartItem, $currencyCode)
        ];
    }

    /**
     * Get Discount Values
     *
     * @param Item $cartItem
     * @param string $currencyCode
     * @return array
     */
    private function getDiscountValues($cartItem, $currencyCode)
    {
        $itemDiscounts = $cartItem->getExtensionAttributes()->getDiscounts();
        if ($itemDiscounts) {
            $discountValues=[];
            foreach ($itemDiscounts as $value) {
                $discount = [];
                $amount = [];
                /* @var \Magento\SalesRule\Api\Data\DiscountDataInterface $discountData */
                $discountData = $value->getDiscountData();
                $discountAmount = $discountData->getAmount();
                $discount['label'] = $value->getRuleLabel() ?: __('Discount');
                $amount['value'] = $discountAmount;
                $amount['currency'] = $currencyCode;
                $discount['amount'] = $amount;
                $discountValues[] = $discount;
            }
            return $discountValues;
        }
        return null;
    }

    private function getCurrentCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function setConvertPrice($price)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $currentCurrencyCode = $this->getCurrentCurrency();
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        if ($baseCurrencyCode == $currentCurrencyCode) {
            return $price;
        } else {
            $price = round($this->priceCurrencyObj->convert($price, $storeId, $currentCurrencyCode), 2);
            return $price;
        }
    }
}
