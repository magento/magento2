<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Cart product configuration model
 */
namespace Magento\Catalog\Model\Product;

class CartConfiguration
{
    /**
     * Decide whether product has been configured for cart or not
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $config
     * @return bool
     */
    public function isProductConfigured(\Magento\Catalog\Model\Product $product, $config)
    {
        // If below POST fields were submitted - this is product's options, it has been already configured
        switch ($product->getTypeId()) {
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL:
                return isset($config['options']);
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
                return isset($config['bundle_option']);
        }
        return false;
    }
}
