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
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Cart\Totals;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\QuoteGraphQl\Model\GetDiscounts;
use Magento\QuoteGraphQl\Model\GetOptionsRegularPrice;

/**
 * @inheritdoc
 */
class CartItemPrices implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * @var Totals|null
     */
    private $totals;

    /**
     * CartItemPrices constructor.
     *
     * @param TotalsCollector $totalsCollector
     * @param GetDiscounts $getDiscounts
     * @param PriceCurrencyInterface $priceCurrency
     * @param GetOptionsRegularPrice $getOptionsRegularPrice
     */
    public function __construct(
        private readonly TotalsCollector $totalsCollector,
        private readonly GetDiscounts $getDiscounts,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly GetOptionsRegularPrice $getOptionsRegularPrice
    ) {
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->totals = null;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
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
        $currencyCode = $cartItem->getQuote()->getQuoteCurrencyCode();

        /** calculate bundle product discount */
        if ($cartItem->getProductType() == 'bundle') {
            $discounts = $cartItem->getExtensionAttributes()->getDiscounts() ?? [];
            $discountAmount = 0;
            foreach ($discounts as $discount) {
                $discountAmount += $discount->getDiscountData()->getAmount();
            }
        } else {
            $discountAmount = $cartItem->getDiscountAmount();
        }

        /**
         * Calculate the actual price of the product with all discounts applied
         */
        $originalItemPrice = $cartItem->getTotalDiscountAmount() > 0
            ? $this->priceCurrency->round(
                $cartItem->getCalculationPrice() - ($cartItem->getTotalDiscountAmount() / max($cartItem->getQty(), 1))
            )
            : $cartItem->getCalculationPrice();

        return [
            'model' => $cartItem,
            'price' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getCalculationPrice(),
            ],
            'price_including_tax' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getPriceInclTax(),
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
                'value' => $discountAmount,
            ],
            'discounts' => $this->getDiscounts->execute(
                $cartItem->getQuote(),
                $cartItem->getExtensionAttributes()->getDiscounts() ?? []
            ),
            'original_item_price' => [
                'currency' => $currencyCode,
                'value' => $originalItemPrice
            ],
            'original_row_total' => [
                'currency' => $currencyCode,
                'value' => $this->getOriginalRowTotal($cartItem),
            ],
        ];
    }

    /**
     * Calculate the original price row total
     *
     * @param Item $cartItem
     * @return float
     */
    private function getOriginalRowTotal(Item $cartItem): float
    {
        $qty = $cartItem->getTotalQty();
        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        return $this->priceCurrency->round($cartItem->getOriginalPrice() + $this->getOptionsPrice($cartItem)) * $qty;
    }

    /**
     * Get the product custom options price
     *
     * @param Item $cartItem
     * @return float
     */
    private function getOptionsPrice(Item $cartItem): float
    {
        $price = 0.0;
        $optionIds = $cartItem->getProduct()->getCustomOption('option_ids');
        if (!$optionIds) {
            return $price;
        }

        foreach (explode(',', $optionIds->getValue() ?? '') as $optionId) {
            $option = $cartItem->getProduct()->getOptionById($optionId);
            $optionValueIds = $cartItem->getOptionByCode('option_' . $optionId);
            if (!$option) {
                return $price;
            }
            if ($option->getRegularPrice()) {
                $price += $option->getRegularPrice();
            } else {
                $price += $this->getOptionsRegularPrice
                    ->execute(explode(",", $optionValueIds->getValue()), $option);
            }
        }

        return $price;
    }
}
