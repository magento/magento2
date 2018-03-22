<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Query\EnumLookup;

/**
 * Post formatting data to set main fields and options for bundle product
 */
class BundleOptions implements FormatterInterface
{
    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * BundleOptions constructor.
     * @param EnumLookup $enumLookup
     */
    public function __construct(EnumLookup $enumLookup)
    {
        $this->enumLookup = $enumLookup;
    }

    /**
     * Add bundle options and options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getTypeId() === Bundle::TYPE_CODE) {
            $productData = $this->formatBundleAttributes($productData);
            $extensionAttributes = $product->getExtensionAttributes();
            $productData['bundle_product_options'] = $extensionAttributes->getBundleProductOptions();
        }

        return $productData;
    }

    /**
     * Format bundle specific top level attributes from product
     *
     * @param array $product
     * @return array
     * @throws RuntimeException
     */
    private function formatBundleAttributes(array $product)
    {
        if (isset($product['price_view'])) {
            $product['price_view']
                = $this->enumLookup->getEnumValueFromField('PriceViewEnum', $product['price_view']);
        }
        if (isset($product['shipment_type'])) {
            $product['ship_bundle_items']
                = $this->enumLookup->getEnumValueFromField('ShipBundleItemsEnum', $product['shipment_type']);
        }
        if (isset($product['price_view'])) {
            $product['dynamic_price'] = !(bool)$product['price_type'];
        }
        if (isset($product['sku_type'])) {
            $product['dynamic_sku'] = !(bool)$product['sku_type'];
        }
        if (isset($product['weight_type'])) {
            $product['dynamic_weight'] = !(bool)$product['weight_type'];
        }
        return $product;
    }
}
