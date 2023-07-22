<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Wishlist\Model\Item\Option;

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
     * @return AmountInterface
     */
    public function getConfiguredAmount(): AmountInterface
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
     * @return AmountInterface
     */
    public function getConfiguredRegularAmount(): AmountInterface
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
        /** @var Product $product */
        $product = parent::getProduct();
        /** @var Option $configurableCustomOption */
        $configurableCustomOption = $product->getCustomOption('option_ids');
        $customPrice = 0;
        if ($configurableCustomOption && $configurableCustomOption->getValue()) {
            $item = $this->item;
            $configurableProduct = $configurableCustomOption->getProduct();
            foreach (explode(',', $configurableCustomOption->getValue()) as $optionId) {
                $option = $configurableProduct->getOptionById($optionId);
                if ($option) {
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
                    /** @var $group DefaultType */
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
        /** @var Product $product */
        $product = parent::getProduct();

        /** @var Option $customOption */
        $customOption = $product->getCustomOption('simple_product');

        return $customOption ? ($customOption->getProduct() ?? $product) : $product;
    }
}
