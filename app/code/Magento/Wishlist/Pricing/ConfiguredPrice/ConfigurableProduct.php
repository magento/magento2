<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

class ConfigurableProduct extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * Get Regular Price Amount object
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getRegularAmount()
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('simple_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
        return $product->getPriceInfo()->getPrice('regular_price')->getAmount();
    }

    /**
     * Get selected option
     *
     * @return \Magento\Wishlist\Model\Item\Option
     */
    public function getOption()
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        return $this->getProduct()->getCustomOption('simple_product');
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('simple_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
        $price = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getValue();

        return max(0, $price);
    }

    /**
     * @inheritdoc
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
