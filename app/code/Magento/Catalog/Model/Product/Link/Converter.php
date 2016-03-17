<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Link;

/**
 * Class Converter
 */
class Converter
{
    /**
     * Convert product links info to array indexed by sku
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return array
     */
    protected function indexBySku(array $products)
    {
        $converted = [];
        foreach($products as $product) {
            $converted[$product->getSku()] = $product;
        }
        return $converted;
    }

    /**
     * @param \Magento\Catalog\Model\Product $entity
     * @return array
     */
    public function convertLinksToGroupedArray($entity)
    {
        $basicData = $entity->getProductLinks();
        $additionalData = $entity->getTypeInstance()->getAssociatedProducts($entity);
        $additionalData = $this->indexBySku($additionalData);

        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
        foreach ($basicData as $link) {
            $info = [];
            $info['id'] = $additionalData[$link->getLinkedProductSku()]->getId();
            $info['sku'] = $link->getLinkedProductSku();
            $info['position'] = $link->getPosition();
            $info = array_merge($info, $link->getExtensionAttributes()->__toArray());
            $linksAsArray[$link->getLinkType()][] = $info;
        }
        return $linksAsArray;
    }
}