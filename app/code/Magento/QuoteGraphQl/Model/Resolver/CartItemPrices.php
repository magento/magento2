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
use Magento\Quote\Model\Cart\Totals;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\QuoteGraphQl\Model\GetDiscounts;

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
     * @param TotalsCollector $totalsCollector
     * @param GetDiscounts $getDiscounts
     */
    public function __construct(
        private readonly TotalsCollector $totalsCollector,
        private readonly GetDiscounts $getDiscounts
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
            )
        ];
    }
}
