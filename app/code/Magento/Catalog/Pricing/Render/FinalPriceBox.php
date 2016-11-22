<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
use Magento\Msrp\Pricing\Price\MsrpPrice;

/**
 * Class for final_price rendering
 *
 * @method bool getUseLinkForAsLowAs()
 * @method bool getDisplayMinimalPrice()
 */
class FinalPriceBox extends BasePriceBox
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (
            !$this->getSaleableItem()
            || $this->getSaleableItem()->getCanShowPrice() === false
            || ($this->getSaleableItem() && !$this->getSaleableItem()->isSalable())
        ) {
            return '';
        }

        $result = parent::_toHtml();

        try {
            /** @var MsrpPrice $msrpPriceType */
            $msrpPriceType = $this->getSaleableItem()->getPriceInfo()->getPrice('msrp_price');
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
            return $this->wrapResult($result);
        }

        //Renders MSRP in case it is enabled
        $product = $this->getSaleableItem();
        if ($msrpPriceType->canApplyMsrp($product) && $msrpPriceType->isMinimalPriceLessMsrp($product)) {
            /** @var BasePriceBox $msrpBlock */
            $msrpBlock = $this->rendererPool->createPriceRender(
                MsrpPrice::PRICE_CODE,
                $this->getSaleableItem(),
                [
                    'real_price_html' => $result,
                    'zone' => $this->getZone(),
                ]
            );
            $result = $msrpBlock->toHtml();
        }

        return $this->wrapResult($result);
    }

    /**
     * Wrap with standard required container
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
            '>' . $html . '</div>';
    }

    /**
     * Render minimal amount
     *
     * @return string
     */
    public function renderAmountMinimal()
    {
        /** @var \Magento\Catalog\Pricing\Price\FinalPrice $price */
        $price = $this->getPriceType(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
        $id = $this->getPriceId() ? $this->getPriceId() : 'product-minimal-price-' . $this->getSaleableItem()->getId();
        return $this->renderAmount(
            $price->getMinimalPrice(),
            [
                'display_label'     => __('As low as'),
                'price_id'          => $id,
                'include_container' => false,
                'skip_adjustments' => true
            ]
        );
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     */
    public function hasSpecialPrice()
    {
        $displayRegularPrice = $this->getPriceType(Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
        $displayFinalPrice = $this->getPriceType(Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        return $displayFinalPrice < $displayRegularPrice;
    }

    /**
     * Define if the minimal price should be shown
     *
     * @return bool
     */
    public function showMinimalPrice()
    {
        /** @var Price\FinalPrice $finalPrice */
        $finalPrice = $this->getPriceType(Price\FinalPrice::PRICE_CODE);
        $finalPriceValue = $finalPrice->getAmount()->getValue();
        $minimalPriceAValue = $finalPrice->getMinimalPrice()->getValue();
        return $this->getDisplayMinimalPrice()
        && $minimalPriceAValue
        && $minimalPriceAValue < $finalPriceValue;
    }

    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getData('list_category_page') ? '-list-category-page': '');
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeys = parent::getCacheKeyInfo();
        $cacheKeys['display_minimal_price'] = $this->getDisplayMinimalPrice();
        return $cacheKeys;
    }
}
