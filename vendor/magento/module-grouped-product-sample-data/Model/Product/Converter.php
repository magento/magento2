<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductSampleData\Model\Product;

/**
 * Convert data for grouped product
 */
class Converter extends \Magento\CatalogSampleData\Model\Product\Converter
{
    /**
     * @inheritdoc
     */
    protected function convertField(&$data, $field, $value)
    {
        if ('associated_sku' == $field) {
            $data['grouped_link_data'] = $this->convertGroupedAssociated($value);
            return true;
        }
        return false;
    }

    /**
     * @param string $associated
     * @return array
     */
    public function convertGroupedAssociated($associated)
    {
        $skuList = explode(',', $associated);
        $data = [];
        $position = 0;
        foreach ($skuList as $sku) {
            $productId = $this->getProductIdBySku($sku);
            if (!$productId) {
                continue;
            }
            $data[$productId] = [
                'id' => $productId,
                'position' => $position++,
                'qty' => '0',
            ];
        }
        return $data;
    }
}
