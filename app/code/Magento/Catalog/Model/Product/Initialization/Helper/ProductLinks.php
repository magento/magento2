<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Initialization\Helper;

class ProductLinks
{
    /**
     * Init product links data (related, upsell, cross sell)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links link data
     * @return \Magento\Catalog\Model\Product
     */
    public function initializeLinks(\Magento\Catalog\Model\Product $product, array $links)
    {
        if (isset($links['related']) && !$product->getRelatedReadonly()) {
            $product->setRelatedLinkData($links['related']);
        }

        if (isset($links['upsell']) && !$product->getUpsellReadonly()) {
            $product->setUpSellLinkData($links['upsell']);
        }

        if (isset($links['crosssell']) && !$product->getCrosssellReadonly()) {
            $product->setCrossSellLinkData($links['crosssell']);
        }

        return $product;
    }
}
