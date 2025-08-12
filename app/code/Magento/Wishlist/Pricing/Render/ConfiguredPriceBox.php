<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\Render;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

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
        /** @var \Magento\Catalog\Pricing\Price\ConfiguredPrice $price */
        $price = $this->getPrice();

        /** @var \Magento\Catalog\Pricing\Render $renderBlock */
        $renderBlock = $this->getRenderBlock();
        if (!$renderBlock && $this->getItem() instanceof ItemInterface) {
            $price->setItem($this->getItem());
        }

        return parent::_prepareLayout();
    }
}
