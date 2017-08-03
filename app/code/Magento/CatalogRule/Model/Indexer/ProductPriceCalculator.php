<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

/**
 * Product price calculation according rules settings.
 * @since 2.2.0
 */
class ProductPriceCalculator
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @since 2.2.0
     */
    private $priceCurrency;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Calculates product price.
     *
     * @param array $ruleData
     * @param null $productData
     * @return float
     * @since 2.2.0
     */
    public function calculate($ruleData, $productData = null)
    {
        if ($productData !== null && isset($productData['rule_price'])) {
            $productPrice = $productData['rule_price'];
        } else {
            $productPrice = $ruleData['default_price'];
        }

        switch ($ruleData['action_operator']) {
            case 'to_fixed':
                $productPrice = min($ruleData['action_amount'], $productPrice);
                break;
            case 'to_percent':
                $productPrice = $productPrice * $ruleData['action_amount'] / 100;
                break;
            case 'by_fixed':
                $productPrice = max(0, $productPrice - $ruleData['action_amount']);
                break;
            case 'by_percent':
                $productPrice = $productPrice * (1 - $ruleData['action_amount'] / 100);
                break;
            default:
                $productPrice = 0;
        }

        return $this->priceCurrency->round($productPrice);
    }
}
