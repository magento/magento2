<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Pricing configuration of wishlist product.
 */
class ConfigurableProduct extends AbstractPrice
{
    /**
<<<<<<< HEAD
     * Price type final.
=======
     * Price type final
>>>>>>> upstream/2.2-develop
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var ItemInterface
     */
    private $item;

    /**
<<<<<<< HEAD
     * Get Configured Price Amount object by price type.
=======
     * Get Configured Price Amount object by price type
>>>>>>> upstream/2.2-develop
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredAmount(): \Magento\Framework\Pricing\Amount\AmountInterface
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('simple_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
<<<<<<< HEAD

=======
>>>>>>> upstream/2.2-develop
        return $product->getPriceInfo()->getPrice(ConfiguredPriceInterface::CONFIGURED_PRICE_CODE)->getAmount();
    }

    /**
<<<<<<< HEAD
     * Get Configured Regular Price Amount object by price type.
=======
     * Get Configured Regular Price Amount object by price type
>>>>>>> upstream/2.2-develop
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredRegularAmount(): \Magento\Framework\Pricing\Amount\AmountInterface
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('simple_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
<<<<<<< HEAD

=======
>>>>>>> upstream/2.2-develop
        return $product->getPriceInfo()->getPrice(ConfiguredPriceInterface::CONFIGURED_REGULAR_PRICE_CODE)->getAmount();
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
