<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\CopyConstructor;

class Grouped implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * Retrieve collection grouped link
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Collection
     */
    protected function _getGroupedLinkCollection(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\Catalog\Model\Product\Link  $links */
        $links = $product->getLinkInstance();
        $links->setLinkTypeId(\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);

        $collection = $links->getLinkCollection();
        $collection->setProduct($product);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }

    /**
     * Build product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        if ($product->getTypeId() !== \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            //do nothing if not grouped product
            return;
        }

        $data = [];
        $attributes = [];
        $link = $product->getLinkInstance();
        $link->setLinkTypeId(\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }

        /** @var \Magento\Catalog\Model\Product\Link $link  */
        foreach ($this->_getGroupedLinkCollection($product) as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }
        $duplicate->setGroupedLinkData($data);
    }
}
