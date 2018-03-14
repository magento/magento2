<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Fixed the id related data in the product data
 */
class EntityIdToId implements FormatterInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(MetadataPool $metadataPool)
    {
        $this->metadataPool = $metadataPool;
    }

    /**
     * Fix entity id data by converting it to an id key
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        $productData['id'] = $product->getData(
            $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
        );

        return $productData;
    }
}
