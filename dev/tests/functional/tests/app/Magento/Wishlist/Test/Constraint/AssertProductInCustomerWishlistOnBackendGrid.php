<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductInCustomerWishlistOnBackendGrid
 * Assert that product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertProductInCustomerWishlistOnBackendGrid extends AbstractConstraint
{
    /**
     * Assert that product is present in grid on customer's wish list tab with configure option and qty
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CustomerIndexEdit $customerIndexEdit, FixtureInterface $product)
    {
        $filter = $this->prepareFilter($product);

        /** @var Grid $wishlistGrid */
        $wishlistGrid = $customerIndexEdit->getCustomerForm()->getTab('wishlist')->getSearchGridBlock();
        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistGrid->isRowVisible($filter),
            'Product ' . $product->getName() . ' is absent in grid with configure option.'
        );
    }

    /**
     * Prepare filter
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareFilter(FixtureInterface $product)
    {
        $checkoutData = $product->getCheckoutData();
        $qty = isset($checkoutData['qty']) ? $checkoutData['qty'] : 1;
        $options = $this->prepareOptions($product);

        return ['product_name' => $product->getName(), 'qty_from' => $qty, 'qty_to' => $qty, 'options' => $options];
    }

    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        $productOptions = [];
        $checkoutData = $product->getCheckoutData()['options'];
        $customOptions = $product->getCustomOptions();
        if (isset($checkoutData['custom_options'])) {
            foreach ($checkoutData['custom_options'] as $option) {
                $optionKey = str_replace('attribute_key_', '', $option['title']);
                $valueKey = str_replace('option_key_', '', $option['value']);
                $productOptions[] = [
                    'option_name' => $customOptions[$optionKey]['title'],
                    'value' => isset($customOptions[$optionKey]['options'][$valueKey]['title'])
                        ? $customOptions[$optionKey]['options'][$valueKey]['title']
                        : $valueKey,
                ];
            }
        }

        return $productOptions;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Product is visible in Customer Wish List on Backend.";
    }
}
