<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Pricing\Price;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

class ConfiguredPrice extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = 'configured_price';

    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return max(0, parent::getValue() + $this->getLinkPrice());
    }

    /**
     * Retrieve calculated links price
     *
     * @return int
     */
    private function getLinkPrice()
    {
        $result = 0;
        if ($this->product->getLinksPurchasedSeparately()) {
            /** @var \Magento\Wishlist\Model\Item\Option $linksIds */
            $linksIds = $this->product->getCustomOption('downloadable_link_ids');
            if ($linksIds) {
                $links = $this->getLinks();
                foreach (explode(',', $linksIds->getValue()) as $linkId) {
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
     */
    private function getLinks()
    {
        /** @var \Magento\Downloadable\Model\Product\Type $productType */
        $productType = $this->product->getTypeInstance();
        $links = $productType->getLinks($this->product);
        return $links;
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
