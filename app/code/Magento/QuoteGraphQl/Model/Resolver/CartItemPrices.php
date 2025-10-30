<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
use Magento\Downloadable\Model\Product\Type;
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
                'value' => $this->getOriginalItemPrice($cartItem),
            ],
            'original_row_total' => [
                'currency' => $currencyCode,
                'value' => $this->getOriginalRowTotal($cartItem),
            ],
        ];
    }

    /**
     * Calculate the original item price, with no discounts or taxes applied
     *
     * @param Item $cartItem
     * @return float
     */
    private function getOriginalItemPrice(Item $cartItem): float
    {
        $originalItemPrice = $cartItem->getOriginalPrice() + $this->getCustomOptionPrice($cartItem);

        // To add downloadable product link price to the original item price
        if ($cartItem->getProductType() === Type::TYPE_DOWNLOADABLE &&
            $cartItem->getProduct()->getData('links_purchased_separately')) {
            $originalItemPrice += (float)$this->getDownloadableLinkPrice($cartItem);
        }

        return $originalItemPrice;
    }

    /**
     * Calculate the original row total price
     *
     * @param Item $cartItem
     * @return float
     */
    private function getOriginalRowTotal(Item $cartItem): float
    {
        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        return $this->priceCurrency->round($this->getOriginalItemPrice($cartItem)) * $cartItem->getTotalQty();
    }

    /**
     * Get the product custom options price
     *
     * @param Item $cartItem
     * @return float
     */
    private function getCustomOptionPrice(Item $cartItem): float
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

    /**
     * Get the downloadable link price
     *
     * @param Item $cartItem
     * @return float
     */
    private function getDownloadableLinkPrice(Item $cartItem): float
    {
        $linksOption = $cartItem->getProduct()->getCustomOption('downloadable_link_ids');
        if (!$linksOption || !$linksOption->getValue()) {
            return 0.0;
        }

        $selectedLinks = array_flip(explode(',', $linksOption->getValue()));
        $downloadableLinks = $cartItem->getProduct()->getTypeInstance()->getLinks($cartItem->getProduct());

        return array_reduce(
            $downloadableLinks,
            fn(float $total, $link) => isset($selectedLinks[$link->getId()]) ?
                $total + (float) $link->getPrice() : $total,
            0.0
        );
    }
}
