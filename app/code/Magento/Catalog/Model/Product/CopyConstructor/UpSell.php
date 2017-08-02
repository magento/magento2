<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\CopyConstructor;

/**
 * Class \Magento\Catalog\Model\Product\CopyConstructor\UpSell
 *
 * @since 2.0.0
 */
class UpSell implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * Build product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     * @since 2.0.0
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        $data = [];
        $link = $product->getLinkInstance();
        $link->useUpSellLinks();
        $attributes = [];
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }
        /** @var \Magento\Catalog\Model\Product\Link $link  */
        foreach ($product->getUpSellLinkCollection() as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }
        $duplicate->setUpSellLinkData($data);
    }
}
