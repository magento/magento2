<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\Render;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPrice;
use Magento\Catalog\Pricing\Render;

class ConfiguredPriceBox extends \Magento\Catalog\Pricing\Render\ConfiguredPriceBox
{
    /**
     * @inheritdoc
     */
    protected function getCacheLifetime()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        /** @var $price ConfiguredPrice */
        $price = $this->getPrice();

        /** @var $renderBlock Render */
        $renderBlock = $this->getRenderBlock();
        if (!$renderBlock && $this->getItem() instanceof ItemInterface) {
            $price->setItem($this->getItem());
        }

        return parent::_prepareLayout();
    }
}
