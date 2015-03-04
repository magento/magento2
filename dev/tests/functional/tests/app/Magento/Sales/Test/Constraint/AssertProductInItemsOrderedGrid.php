<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\Create\Items;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert product was added to Items Ordered grid in customer account on Order creation page backend.
 */
class AssertProductInItemsOrderedGrid extends AbstractAssertForm
{
    /**
     * Fields for assert.
     *
     * @var array
     */
    protected $fields = ['name' => '', 'price' => '', 'checkout_data' => ['qty' => '']];

    /**
     * Check configured products.
     *
     * @var bool
     */
    protected $productsIsConfigured;

    /**
     * Assert product was added to Items Ordered grid in customer account on Order creation page backend.
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $products
     * @param bool $productsIsConfigured
     * @throws \Exception
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex, array $products, $productsIsConfigured = true)
    {
        if (!$products) {
            throw new \Exception("No products");
        }
        $this->productsIsConfigured = $productsIsConfigured;
        $data = $this->prepareData($products, $orderCreateIndex->getCreateBlock()->getItemsBlock());

        \PHPUnit_Framework_Assert::assertEquals(
            $data['fixtureData'],
            $data['pageData'],
            'Product data on order create page not equals to passed from fixture.'
        );
    }

    /**
     * Prepare data.
     *
     * @param array $data
     * @param Items $itemsBlock
     * @return array
     */
    protected function prepareData(array $data, Items $itemsBlock)
    {
        $fixtureData = [];
        foreach ($data as $product) {
            $checkoutData = $product->getCheckoutData();
            $fixtureData[] = [
                'name' => $product->getName(),
                'price' => number_format($this->getProductPrice($product), 2),
                'checkout_data' => [
                    'qty' => $this->productsIsConfigured && isset($checkoutData['qty']) ? $checkoutData['qty'] : 1,
                ],
            ];
        }
        $pageData = $itemsBlock->getProductsDataByFields($this->fields);
        $preparePageData = $this->arraySort($fixtureData, $pageData);

        return ['fixtureData' => $fixtureData, 'pageData' => $preparePageData];
    }

    /**
     * Sort of array.
     *
     * @param array $fixtureData
     * @param array $pageData
     * @return array
     */
    protected function arraySort(array $fixtureData, array $pageData)
    {
        $result = [];
        foreach ($fixtureData as $key => $value) {
            foreach ($pageData as $pageDataKey => $pageDataValue) {
                if ($value['name'] == $pageDataValue['name']) {
                    $result[$key] = $pageDataValue;
                    unset($pageData[$pageDataKey]);
                    break;
                }
            }
        }
        return array_merge($result, $pageData);
    }

    /**
     * Get product price.
     *
     * @param FixtureInterface $product
     * @return int
     */
    protected function getProductPrice(FixtureInterface $product)
    {
        return isset($product->getCheckoutData()['cartItem']['price'])
            ? $product->getCheckoutData()['cartItem']['price']
            : $product->getPrice();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is added to Items Ordered grid from "Last Ordered Items" section on Order creation page.';
    }
}
