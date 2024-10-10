<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\OrderItemPrices;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Item;
use Magento\QuoteGraphQl\Model\GetOptionsRegularPrice;

/**
 * Prices data provider for order item
 */
class PricesProvider
{
    /**
     * PricesProvider constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param GetOptionsRegularPrice $getOptionsRegularPrice
     */
    public function __construct(
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly GetOptionsRegularPrice $getOptionsRegularPrice
    ) {
    }

    /**
     * Returns an array of different prices applied on the order item
     *
     * @param Item $orderItem
     * @return array
     */
    public function execute(Item $orderItem): array
    {
        $currency = $orderItem->getOrder()->getOrderCurrencyCode();
        return [
            'model' => $orderItem,
            'price' => [
                'currency' => $currency,
                'value' => $orderItem->getPrice() ?? 0
            ],
            'price_including_tax' => [
                'currency' => $currency,
                'value' => $orderItem->getPriceInclTax() ?? 0
            ],
            'row_total' => [
                'currency' => $currency,
                'value' => $orderItem->getRowTotal() ?? 0
            ],
            'row_total_including_tax' => [
                'currency' => $currency,
                'value' => $orderItem->getRowTotalInclTax() ?? 0
            ],
            'total_item_discount' => [
                'currency' => $currency,
                'value' => $orderItem->getDiscountAmount() ?? 0
            ],
            'original_price' => [
                'currency' => $currency,
                'value' => $orderItem->getOriginalPrice()
            ],
            'original_price_including_tax' => [
                'currency' => $currency,
                'value' => $this->getOriginalPriceInclTax($orderItem)
            ],
            'original_row_total' => [
                'currency' => $currency,
                'value' => $this->getOriginalRowTotal($orderItem)
            ],
            'original_row_total_including_tax' => [
                'currency' => $currency,
                'value' => $this->getOriginalRowTotalInclTax($orderItem)
            ]
        ];
    }

    /**
     * Calculate the original price including tax
     *
     * @param Item $orderItem
     * @return float
     */
    private function getOriginalPriceInclTax(Item $orderItem): float
    {
        return $orderItem->getOriginalPrice() * (1 + ($orderItem->getTaxPercent() / 100));
    }

    /**
     * Calculate the original row total price including tax
     *
     * @param Item $orderItem
     * @return float
     */
    private function getOriginalRowTotalInclTax(Item $orderItem): float
    {
        return $this->getOriginalRowTotal($orderItem) * (1 + ($orderItem->getTaxPercent() / 100));
    }

    /**
     * Calculate the original price row total
     *
     * @param Item $orderItem
     * @return float
     */
    private function getOriginalRowTotal(Item $orderItem): float
    {
        $qty = $orderItem->getQtyOrdered();
        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        return $this->priceCurrency->round($orderItem->getOriginalPrice() + $this->getOptionsPrice($orderItem)) * $qty;
    }

    /**
     * Get the product custom options price
     *
     * @param Item $orderItem
     * @return float
     */
    private function getOptionsPrice(Item $orderItem): float
    {
        $price = 0.0;
        $productOptions = $orderItem->getProductOptions();
        if (empty($productOptions['options'])) {
            return $price;
        }

        foreach ($productOptions['options'] as $option) {
            $productOption = $orderItem->getProduct()->getOptionById($option['option_id']);
            if ($productOption->getRegularPrice()) {
                $price += $productOption->getRegularPrice();
            } elseif (!empty($option['option_value'])) {
                $price += $this->getOptionsRegularPrice
                    ->execute(explode(",", $option['option_value']), $productOption);
            }
        }
        return $price;
    }
}
