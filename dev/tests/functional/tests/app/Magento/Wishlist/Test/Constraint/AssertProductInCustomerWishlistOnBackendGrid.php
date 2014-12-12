<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductInCustomerWishlistOnBackendGrid
 * Assert that product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertProductInCustomerWishlistOnBackendGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
        $wishlistGrid = $customerIndexEdit->getCustomerForm()->getTabElement('wishlist')->getSearchGridBlock();
        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistGrid->isRowVisible($filter, true, false),
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
        return "Product is visible in customer wishlist on backend.";
    }
}
