<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

use \Magento\Catalog\Api\Data\ProductInterface;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Grabs the initial data from the product and fixes the id
 */
class IdFixer implements FormatterInterface
{
    /**
     * Fix entity id data
     *
     * {@inheritdoc}
     */
    public function format(ProductInterface $product, array $productData = [])
    {
        $productData = $product->getData();
        $productData['id'] = $product->getId();
        unset($productData['entity_id']);

        return $productData;
    }
}
