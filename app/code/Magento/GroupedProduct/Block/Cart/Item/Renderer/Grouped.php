<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Cart\Item\Renderer;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Shopping cart item render block
 *
 * @api
 * @since 100.0.2
 */
class Grouped extends Renderer implements IdentityInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     * @deprecated moved to model because of class refactoring
     * @see \Magento\GroupedProduct\Model\Product\Configuration\Item\ItemProductResolver::CONFIG_THUMBNAIL_SOURCE
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/grouped_product_image';

    /**
     * Get item grouped product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getGroupedProduct()
    {
        $option = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [parent::getIdentities()];
        if ($this->getItem()) {
            $identities[] = $this->getGroupedProduct()->getIdentities();
        }
        return array_merge([], ...$identities);
    }
}
