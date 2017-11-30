<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Render;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Class for configured_price rendering
 */
class ConfiguredPriceBox extends FinalPriceBox
{
    /**
     * Retrieve an item instance to the configured price model
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var $price \Magento\Catalog\Pricing\Price\ConfiguredPrice */
        $price = $this->getPrice();
        /** @var $renderBlock \Magento\Catalog\Pricing\Render */
        $renderBlock = $this->getRenderBlock();
        if ($renderBlock && $renderBlock->getItem() instanceof ItemInterface) {
            $price->setItem($renderBlock->getItem());
        } elseif ($renderBlock
            && $renderBlock->getParentBlock()
            && $renderBlock->getParentBlock()->getItem() instanceof ItemInterface
        ) {
            $price->setItem($renderBlock->getParentBlock()->getItem());
        }
        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceType($priceCode)
    {
        $price = $this->saleableItem->getPriceInfo()->getPrice($priceCode);
        $item = $this->getData('item');
        if ($price instanceof \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface
        && $item instanceof \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface) {
            $price->setItem($item);
        }
        return $price;
    }

    /**
     * @return PriceInterface
     */
    public function getConfiguredPrice()
    {
        /** @var \Magento\Bundle\Pricing\Price\ConfiguredPrice $configuredPrice */
        $configuredPrice = $this->getPrice();
        if (empty($configuredPrice->getSelectionPriceList())) {
            // If there was no selection we must show minimal regular price
            return $this->getSaleableItem()->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
        }

        return $configuredPrice;
    }

    /**
     * @return PriceInterface
     */
    public function getConfiguredRegularPrice()
    {
        /** @var \Magento\Bundle\Pricing\Price\ConfiguredPrice $configuredPrice */
        $configuredPrice = $this->getPriceType(ConfiguredPriceInterface::CONFIGURED_REGULAR_PRICE_CODE);
        if (empty($configuredPrice->getSelectionPriceList())) {
            // If there was no selection we must show minimal regular price
            return $this->getSaleableItem()->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE);
        }

        return $configuredPrice;
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     */
    public function hasSpecialPrice()
    {
        if ($this->price->getPriceCode() == ConfiguredPriceInterface::CONFIGURED_PRICE_CODE) {
            $displayRegularPrice = $this->getConfiguredRegularPrice()->getAmount()->getValue();
            $displayFinalPrice = $this->getConfiguredPrice()->getAmount()->getValue();
            return $displayFinalPrice < $displayRegularPrice;
        }
        return parent::hasSpecialPrice();
    }
}
