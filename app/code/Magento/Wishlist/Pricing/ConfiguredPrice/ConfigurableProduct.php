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
     * Price type final.
     */
    public const PRICE_CODE = 'final_price';

    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * Get Configured Price Amount object by price type.
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredAmount(): \Magento\Framework\Pricing\Amount\AmountInterface
    {
        return $this
            ->getProduct()
            ->getPriceInfo()
            ->getPrice(ConfiguredPriceInterface::CONFIGURED_PRICE_CODE)
            ->getAmount();
    }

    /**
     * Get Configured Regular Price Amount object by price type.
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredRegularAmount(): \Magento\Framework\Pricing\Amount\AmountInterface
    {
        return $this
            ->getProduct()
            ->getPriceInfo()
            ->getPrice(ConfiguredPriceInterface::CONFIGURED_REGULAR_PRICE_CODE)
            ->getAmount();
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $price = $this->getProduct()->getPriceInfo()->getPrice(self::PRICE_CODE)->getValue();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = parent::getProduct();
        /** @var \Magento\Wishlist\Model\Item\Option $configurableCustomOption */
        $configurableCustomOption = $product->getCustomOption('option_ids');
        $customPrice = 0;
        if ($configurableCustomOption && $configurableCustomOption->getValue()) {
            $item = $this->item;
            $configurableProduct = $configurableCustomOption->getProduct();
            foreach (explode(',', $configurableCustomOption->getValue()) as $optionId) {
                $option = $configurableProduct->getOptionById($optionId);
                if ($option) {
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
                    /** @var $group \Magento\Catalog\Model\Product\Option\Type\DefaultType */
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option);
                    $customPrice += $group->getOptionPrice($itemOption->getValue(), $price);
                }
            }
        }
        if ($customPrice) {
            $price = $price + $customPrice;
        }
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

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = parent::getProduct();

        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $product->getCustomOption('simple_product');

        return $customOption ? ($customOption->getProduct() ?? $product) : $product;
    }
}
