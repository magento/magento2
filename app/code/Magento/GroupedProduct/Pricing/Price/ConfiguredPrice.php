<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;

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
        $value = 0.;
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
        $typeInstance = $this->getProduct()->getTypeInstance();
        $associatedProducts = $typeInstance
            ->setStoreFilter($this->getProduct()->getStore(), $this->getProduct())
            ->getAssociatedProducts($this->getProduct());

        foreach ($associatedProducts as $product) {
            /** @var Product $product */
            /** @var \Magento\Wishlist\Model\Item\Option $customOption */
            $customOption = $this->getProduct()
                ->getCustomOption('associated_product_' . $product->getId());
            if (!$customOption) {
                continue;
            }
            $finalPrice = $product->getPriceInfo()
                ->getPrice(FinalPrice::PRICE_CODE)
                ->getValue();
            $value += $finalPrice * ($customOption->getValue() ? $customOption->getValue() : 1);
        }
        return $value;
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
