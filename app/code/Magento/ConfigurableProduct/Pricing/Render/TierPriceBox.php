<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Pricing\Price\TierPrice;

/**
 * Responsible for displaying tier price box on configurable product page.
 *
 * @package Magento\ConfigurableProduct\Pricing\Render
 */
class TierPriceBox extends FinalPriceBox
{
    // Price display settings
    const DISPLAY_TIER_PRICE_EXCLUDING_TAX = 'priceExclTax';
    const DISPLAY_TIER_PRICE_INCLUDING_TAX = 'price';
    const DISPLAY_TIER_PRICE_BOTH = 'both';

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        // Hide tier price block in case of MSRP or in case when no options with tier price.
        if (!$this->isMsrpPriceApplicable() && $this->isTierPriceApplicable()) {
            return parent::toHtml();
        }
    }

    /**
     * Check if at least one of simple products has tier price.
     *
     * @return bool
     */
    private function isTierPriceApplicable()
    {
        $product = $this->getSaleableItem();
        foreach ($product->getTypeInstance()->getUsedProducts($product) as $simpleProduct) {
            if ($simpleProduct->isSalable() &&
                !empty($simpleProduct->getPriceInfo()->getPrice(TierPrice::PRICE_CODE)->getTierPriceList())
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get price display settings
     *
     * @return string
     */
    public function getTaxDisplayType()
    {
        switch ($this->_scopeConfig->getValue('tax/display/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORES)) {
            case \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX:
                return self::DISPLAY_TIER_PRICE_EXCLUDING_TAX;
                break;
            case \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX:
                return self::DISPLAY_TIER_PRICE_INCLUDING_TAX;
                break;
            case \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH:
                return self::DISPLAY_TIER_PRICE_BOTH;
                break;
        }
    }
}
