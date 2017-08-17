<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutCartConfigure;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assertion that bundle product is correctly displayed on cart configuration page.
 */
class AssertBundleProductOnConfigureCartPage extends AbstractAssertForm
{
    /**
     * Check bundle product options correctly displayed on cart configuration page.
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @param CheckoutCartConfigure $checkoutCartConfigure
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart, CheckoutCartConfigure $checkoutCartConfigure)
    {
        $checkoutCart->open();
        $sourceProducts = $cart->getDataFieldConfig('items')['source'];
        $products = $sourceProducts->getProducts();
        foreach ($cart->getItems() as $key => $item) {
            $product = $products[$key];
            $cartItem = $checkoutCart->getCartBlock()->getCartItem($product);
            $cartItem->edit();
            $options = $checkoutCartConfigure->getBundleViewBlock()->getBundleBlock()->getOptions($product, true);
            $this->checkOptions($product, $options, $item->getData()['options']);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle options data on cart configuration page is correct.';
    }

    /**
     * Compare bundle product options from fixture with product form.
     *
     * @param BundleProduct $product
     * @param array $formOptions
     * @param array $cartItemOptions
     * @return void
     */
    private function checkOptions(BundleProduct $product, array $formOptions, array $cartItemOptions)
    {
        $productOptions = $this->prepareBundleOptions($product, $cartItemOptions);
        $productOptions = $this->sortDataByPath($productOptions, '::title');
        foreach ($productOptions as $key => $productOption) {
            $productOptions[$key] = $this->sortDataByPath($productOption, 'options::title');
        }
        $formOptions = $this->sortDataByPath($formOptions, '::title');
        foreach ($formOptions as $key => $formOption) {
            $formOptions[$key] = $this->sortDataByPath($formOption, 'options::title');
        }

        $error = $this->verifyData($productOptions, $formOptions);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Prepare bundle options.
     *
     * @param BundleProduct $product
     * @param array $cartItemOptions
     * @return array
     */
    private function prepareBundleOptions(BundleProduct $product, array $cartItemOptions)
    {
        $bundleSelections = $product->getBundleSelections();
        $bundleOptions = isset($bundleSelections['bundle_options']) ? $bundleSelections['bundle_options'] : [];
        $result = [];
        foreach ($bundleOptions as $optionKey => $bundleOption) {
            $optionData = [
                'title' => $bundleOption['title'],
                'type' => $bundleOption['frontend_type'],
                'is_require' => $bundleOption['required'],
            ];
            $key = 0;
            foreach ($bundleOption['assigned_products'] as $productKey => $assignedProduct) {
                if ($this->isInStock($product, $key++)) {
                    $price = isset($assignedProduct['data']['selection_price_value'])
                        ? $assignedProduct['data']['selection_price_value']
                        : $bundleSelections['products'][$optionKey][$productKey]->getPrice();
                    $title = $assignedProduct['search_data']['name'];
                    $optionData['options'][$productKey] = [
                        'title' => $title,
                        'price' => number_format($price, 2),
                    ];
                    foreach ($cartItemOptions as $option) {
                        if (strpos($option['value'], $title)) {
                            $optionData['options'][$productKey]['selected'] = true;
                        }
                    }
                }
            }
            $result[$optionKey] = $optionData;
        }

        return $result;
    }

    /**
     * Check product is in stock.
     *
     * @param BundleProduct $product
     * @param int $key
     * @return bool
     */
    private function isInStock(BundleProduct $product, int $key)
    {
        $assignedProducts = $product->getBundleSelections()['products'][0];
        $status = $assignedProducts[$key]->getData()['quantity_and_stock_status']['is_in_stock'];

        return $status === 'In Stock';
    }
}
