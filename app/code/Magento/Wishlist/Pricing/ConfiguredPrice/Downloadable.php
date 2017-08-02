<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class \Magento\Wishlist\Pricing\ConfiguredPrice\Downloadable
 *
 * @since 2.1.0
 */
class Downloadable extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = 'configured_price';

    /**
     * @var ItemInterface
     * @since 2.1.0
     */
    private $item;

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function getValue()
    {
        return max(0, parent::getValue() + $this->getLinkPrice());
    }

    /**
     * Retrieve calculated links price
     *
     * @return int
     * @since 2.1.0
     */
    private function getLinkPrice()
    {
        $result = 0;
        if ($this->getProduct()->getLinksPurchasedSeparately()) {
            /** @var \Magento\Wishlist\Model\Item\Option $customOption */
            $customOption = $this->getProduct()->getCustomOption('downloadable_link_ids');
            if ($customOption) {
                $links = $this->getLinks();
                $linkIds = explode(',', $customOption->getValue());
                foreach ($linkIds as $linkId) {
                    if (isset($links[$linkId])) {
                        $result += $links[$linkId]->getPrice();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return \Magento\Downloadable\Model\Link[]
     * @since 2.1.0
     */
    private function getLinks()
    {
        /** @var \Magento\Downloadable\Model\Product\Type $productType */
        $productType = $this->getProduct()->getTypeInstance();
        $links = $productType->getLinks($this->getProduct());
        return $links;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
