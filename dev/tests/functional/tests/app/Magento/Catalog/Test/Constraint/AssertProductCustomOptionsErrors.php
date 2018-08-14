<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that every required product's Custom Option contains JS validation error.
 */
class AssertProductCustomOptionsErrors extends AbstractConstraint
{
    /**
     * Assert that every required product's Custom Option contains JS validation error.
     *
     * @param CatalogProductView $catalogProductView
     * @param array $products
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        array $products
    ) {
        foreach ($products as $product) {
            foreach ($product->getData('custom_options') as $option) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $catalogProductView->getCustomOptionsBlock()->isJsMessageVisible($option['title']),
                    'Required Custom Option ' . $option['title'] . " doesn't contain JS validation error."
                );
            }
        }
    }

    /**
     * Assert success message that every required product's Custom Option contains JS validation error.
     *
     * @return string
     */
    public function toString()
    {
        return "Every required product's Custom Option contains JS validation error.";
    }
}
