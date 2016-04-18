<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Composite;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Configure
 * Adminhtml grouped product composite configure block
 */
class Configure extends \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
{
    /**
     * Fill options for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $data = $this->prepareData($product->getData());
        $this->_fill($data);
    }

    /**
     * Prepare data
     *
     * @param array $fields
     * @return array
     */
    protected function prepareData(array $fields)
    {
        $productOptions = [];
        $checkoutData = $fields['checkout_data']['options'];
        if (count($checkoutData)) {
            $qtyMapping = $this->dataMapping(['qty' => '']);
            $selector = $qtyMapping['qty']['selector'];
            $assignedProducts = $fields['associated']['assigned_products'];
            foreach ($checkoutData as $key => $item) {
                $productName = $assignedProducts[str_replace('product_key_', '', $item['name'])]['name'];
                $qtyMapping['qty']['selector'] = str_replace('%product_name%', $productName, $selector);
                $qtyMapping['qty']['value'] = $item['qty'];
                $productOptions['product_' . $key] = $qtyMapping['qty'];
            }
        }

        return $productOptions;
    }
}
