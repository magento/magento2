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
<<<<<<< HEAD
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
=======
    public function setItem(ItemInterface $item): ConfiguredRegularPrice
    {
        $this->item = $item;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $this;
    }

    /**
<<<<<<< HEAD
     * Calculate configured price
     *
     * @return float
     */
    protected function calculatePrice()
=======
     * Calculate configured price.
     *
     * @return float
     */
    protected function calculatePrice(): float
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $value;
    }

    /**
<<<<<<< HEAD
     * Price value of product with configured options
=======
     * Price value of product with configured options.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            return $this->value;
        }
    }
}
