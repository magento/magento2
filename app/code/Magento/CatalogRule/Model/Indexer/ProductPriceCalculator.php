<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

class ProductPriceCalculator
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * ProductPriceCalculator constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param array $ruleData
     * @param null $productData
     * @return float
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
