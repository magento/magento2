<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\CopyConstructor;

/**
 * Class \Magento\Catalog\Model\Product\CopyConstructor\CrossSell
 *
 * @since 2.0.0
 */
class CrossSell implements \Magento\Catalog\Model\Product\CopyConstructorInterface
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
        $attributes = [];
        $link = $product->getLinkInstance();
        $link->useCrossSellLinks();
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }
        /** @var \Magento\Catalog\Model\Product\Link $link  */
        foreach ($product->getCrossSellLinkCollection() as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }
        $duplicate->setCrossSellLinkData($data);
    }
}
