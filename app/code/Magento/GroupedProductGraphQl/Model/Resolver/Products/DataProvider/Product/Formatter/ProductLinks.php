<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Format the product links information to conform to GraphQL schema representation
 */
class ProductLinks implements FormatterInterface
{
    const LINK_TYPE = 'associated';

    /**
     * Format product links data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        $productLinks = $product->getProductLinks();
        if ($productLinks && $product->getTypeId() === Grouped::TYPE_CODE) {
            /** @var Link $productLink */
            foreach ($productLinks as $productLinkKey => $productLink) {
                if ($productLink->getLinkType() === self::LINK_TYPE) {
                    $data['product'] = $productLink->getData();
                    $data['qty'] = $productLink->getExtensionAttributes()->getQty();
                    $data['position'] = (int)$productLink->getPosition();
                    $productData['items'][$productLinkKey] = $data;
                }
            }
        } else {
            $productData['items'] = null;
        }

        return $productData;
    }
}
