<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Link;

/**
 * Class Converter
 *
 * @api
 * @since 101.0.0
 */
class Converter
{
    /**
     * Convert product links info to array indexed by sku
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return array
     * @since 101.0.0
     */
    protected function indexBySku(array $products)
    {
        $converted = [];
        foreach ($products as $product) {
            $converted[$product->getSku()] = $product;
        }
        return $converted;
    }

    /**
     * @param \Magento\Catalog\Model\Product $entity
     * @return array
     * @since 101.0.0
     */
    public function convertLinksToGroupedArray($entity)
    {
        $basicData = $entity->getProductLinks();
        $associatedProducts = $entity->getTypeInstance()->getAssociatedProducts($entity);
        $associatedProducts = $this->indexBySku($associatedProducts);
        $linksAsArray = [];
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
        foreach ($basicData as $link) {
            $info = $link->getData();
            if ($link->getLinkType() == 'associated') {
                $info['id'] = $associatedProducts[$link->getLinkedProductSku()]->getId();
            }
            $info = array_merge($info, $link->getExtensionAttributes()->__toArray());
            $linksAsArray[$link->getLinkType()][] = $info;
        }
        return $linksAsArray;
    }
}
