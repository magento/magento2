<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\Create\Items;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGroupedProductInItemsOrderedGrid
 * Assert grouped product was added to Items Ordered grid in customer account on Order creation page backend
 */
class AssertGroupedProductInItemsOrderedGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Fields for assert
     *
     * @var array
     */
    protected $fields = ['name' => '', 'price' => '', 'checkout_data' => ['qty' => '']];

    /**
     * Check configured products
     *
     * @var bool
     */
    protected $productsIsConfigured;

    /**
     * Assert product was added to Items Ordered grid in customer account on Order creation page backend
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $products
     * @throws \Exception
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex, array $products)
    {
        if (empty($products)) {
            throw new \Exception("No products");
        }
        $data = $this->prepareData($products, $orderCreateIndex->getCreateBlock()->getItemsBlock());

        \PHPUnit_Framework_Assert::assertEquals(
            $data['fixtureData'],
            $data['pageData'],
            'Grouped product data on order create page not equals to passed from fixture.'
        );
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @param Items $itemsBlock
     * @return array
     */
    protected function prepareData(array $data, Items $itemsBlock)
    {
        $fixtureData = [];
        foreach ($data as $product) {
            $products = $product->getAssociated()['products'];
            foreach ($products as $key => $value) {
                $fixtureData[$key]['name'] = $value->getName();
                $fixtureData[$key]['price'] = number_format($value->getPrice(), 2);
            }
            $options = $product->getCheckoutData()['options'];
            foreach ($options as $key => $option) {
                $fixtureData[$key]['checkout_data']['qty'] = $option['qty'];
            }
        }
        $pageData = $itemsBlock->getProductsDataByFields($this->fields);

        return ['fixtureData' => $fixtureData, 'pageData' => $pageData];
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is added to Items Ordered grid from "Last Ordered Items" section on Order creation page.';
    }
}
