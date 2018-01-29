<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductGraphQl\Model\Resolver\Products\Query;

use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProduct;
use Magento\Catalog\Model\ProductLink\Link;

/**
 * Retrieves simple product data for child products, and formats group product data
 */
class GroupedProductPostProcessor implements \Magento\Framework\GraphQl\Query\PostFetchProcessorInterface
{
    /**
     * Process all grouped product data, including adding children product data and formatting relevant attributes.
     *
     * @param array $resultData
     * @return array
     */
    public function process(array $resultData)
    {
        foreach ($resultData as $productKey => $product) {
            if ($product['type_id'] === GroupedProduct::TYPE_CODE) {
                if (isset($product['product_links'])) {
                    foreach ($product['product_links'] as $productLinkKey => $productlink) {
                        $resultData[$productKey]['product_links'][$productLinkKey]
                            = $this->formatProductLinks($productlink);
                    }
                }
            }
        }

        return $resultData;
    }

    /**
     * Format product links
     *
     * @param Link $link
     * @return array
     */
    private function formatProductLinks(Link $link)
    {
        $returnData = $link->getData();
        $returnData['qty'] = $link->getExtensionAttributes()->getQty();
        return $returnData;
    }
}
