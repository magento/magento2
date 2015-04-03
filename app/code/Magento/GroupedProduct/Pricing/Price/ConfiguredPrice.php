<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;

class ConfiguredPrice extends CatalogFinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = self::CONFIGURED_PRICE_CODE;

    /**
     * @var null|ItemInterface
     */
    protected $item;

    /**
     * @var float
     */
    protected $configuredPrice;

    /**
     * @param ItemInterface $item
     * @return $this
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Calculate configured price
     *
     * @return float
     */
    protected function calculatePrice()
    {
        if (!$this->configuredPrice) {
            /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
            $typeInstance = $this->getProduct()->getTypeInstance();
            $associatedProducts = $typeInstance
                ->setStoreFilter($this->getProduct()->getStore(), $this->getProduct())
                ->getAssociatedProducts($this->getProduct());

            foreach ($associatedProducts as $product) {
                /** @var \Magento\Catalog\Model\Product $product */
                /** @var \Magento\Wishlist\Model\Item\Option $customOption */
                $customOption = $this->getProduct()
                    ->getCustomOption('associated_product_' . $product->getId());
                if (!$customOption) {
                    continue;
                }
                $finalPrice = $product->getPriceInfo()
                    ->getPrice(FinalPrice::PRICE_CODE)
                    ->getValue();
                $this->configuredPrice += $finalPrice * ($customOption->getValue() ? $customOption->getValue() : 1);
            }
        }
        return $this->configuredPrice;
    }

    /**
     * Price value of product with configured options
     *
     * @return bool|float
     */
    public function getValue()
    {
        return $this->item ? $this->calculatePrice() : parent::getValue();
    }
}
