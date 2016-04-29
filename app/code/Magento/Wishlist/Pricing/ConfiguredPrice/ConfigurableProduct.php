<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @inheritdoc
     */
    public function getValue()
    {
        $result = 0.;
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('simple_product');
        if ($customOption) {
            /** @var \Magento\Framework\Pricing\PriceInfoInterface $priceInfo */
            $priceInfo = $customOption->getProduct()->getPriceInfo();
            $result = $priceInfo->getPrice(self::PRICE_CODE)->getValue();
        }
        return max(0, $result);
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
