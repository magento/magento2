<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Pricing\Render;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class \Magento\Wishlist\Pricing\Render\ConfiguredPriceBox
 *
 */
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
        /** @var $price \Magento\Catalog\Pricing\Price\ConfiguredPrice */
        $price = $this->getPrice();

        /** @var $renderBlock \Magento\Catalog\Pricing\Render */
        $renderBlock = $this->getRenderBlock();
        if (!$renderBlock && $this->getItem() instanceof ItemInterface) {
            $price->setItem($this->getItem());
        }

        return parent::_prepareLayout();
    }
}
