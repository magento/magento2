<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RelatedProductGraphQl\Model\DataProvider\Products;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\LinkFactory;

/**
 * Related Products Data Provider
 */
class LinkedProductsDataProvider
{
    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @param LinkFactory $linkFactory
     */
    public function __construct(LinkFactory $linkFactory)
    {
        $this->linkFactory = $linkFactory;
    }

    /**
     * Get Related Products by Product and Link Type
     *
     * @param Product $product
     * @param array $fields
     * @param int $linkType
     * @return Product[]
     */
    public function getRelatedProducts(Product $product, array $fields, int $linkType): array
    {
        /** @var Link $link */
        $link = $this->linkFactory->create([ 'data' => [
            'link_type_id' => $linkType
        ]]);

        $collection = $link->getProductCollection();
        $collection->setIsStrongMode();
        foreach ($fields as $field) {
            $collection->addAttributeToSelect($field);
        }
        $collection->setProduct($product);

        return $collection->getItems();
    }
}
