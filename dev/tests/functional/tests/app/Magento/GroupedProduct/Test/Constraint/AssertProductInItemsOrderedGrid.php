<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

/**
 * Assert product was added to Items Ordered grid in customer account on Order creation page backend.
 */
class AssertProductInItemsOrderedGrid extends \Magento\Sales\Test\Constraint\AssertProductInItemsOrderedGrid
{
    /**
     * Prepare data.
     *
     * @param array $data
     * @param \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items $itemsBlock
     * @return array
     */
    protected function prepareData(array $data, \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items $itemsBlock)
    {
        $fixtureData = [];
        foreach ($data as $product) {
            $fixtureData = array_merge($fixtureData, $this->getOptionsDetails($product));
        }
        $pageData = $itemsBlock->getProductsDataByFields($this->fields);
        $preparePageData = $this->arraySort($fixtureData, $pageData);
        return ['fixtureData' => $fixtureData, 'pageData' => $preparePageData];
    }

    /**
     * Get product options details.
     *
     * @param \Magento\Mtf\Fixture\FixtureInterface $product
     * @return array
     */
    private function getOptionsDetails(\Magento\Mtf\Fixture\FixtureInterface $product)
    {
        /** @var \Magento\GroupedProduct\Test\Fixture\GroupedProduct  $product */
        $fixtureProducts = [];
        $optionsPrices = $this->getProductPrice($product);
        $optionsQtys = $product->getCheckoutData()['cartItem']['qty'];
        $assignedProducts = $product->getAssociated()['assigned_products'];

        foreach ($assignedProducts as $key => $assignedProduct) {
            $fixtureProducts[] = [
                'name' => $assignedProduct['name'],
                'price' => number_format($optionsPrices['product_key_' . $key], 2),
                'checkout_data' => [
                    'qty' => $this->productsIsConfigured ? $optionsQtys['product_key_' . $key] : 1
                ]
            ];
        }
        return $fixtureProducts;
    }
}
