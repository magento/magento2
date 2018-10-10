<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;

/**
 * Configured regular price model.
 */
class ConfiguredRegularPrice extends AbstractPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = ConfiguredPriceInterface::CONFIGURED_REGULAR_PRICE_CODE;

    /**
     * @var null|ItemInterface
     */
    private $item;

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
                ->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
                ->getValue();
            $value += $finalPrice * ($customOption->getValue() ?: 1);
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
        if ($this->item) {
            return $this->calculatePrice();
        } else {
            if ($this->value === null) {
                $price = $this->product->getPrice();
                $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
                $this->value = $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : false;
            }
            return $this->value;
        }
    }
}
