<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Cart\Item\Renderer;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Shopping cart item render block for configurable products.
 *
 * @api
 * @since 100.0.2
 */
class Configurable extends Renderer implements IdentityInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     * @deprecated moved to model because of class refactoring
     * @see \Magento\ConfigurableProduct\Model\Product\Configuration\Item\ItemProductResolver::CONFIG_THUMBNAIL_SOURCE
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/configurable_product_image';

    /**
     * Get item configurable child product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getChildProduct()
    {
        if ($option = $this->getItem()->getOptionByCode('simple_product')) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get item product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * Get list of all options for product
     *
     * @return array
     */
    public function getOptionList()
    {
        return $this->_productConfig->getOptions($this->getItem());
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = parent::getIdentities();
        if ($this->getItem()) {
            $identities = array_merge($identities, $this->getChildProduct()->getIdentities());
        }
        return $identities;
    }
}
