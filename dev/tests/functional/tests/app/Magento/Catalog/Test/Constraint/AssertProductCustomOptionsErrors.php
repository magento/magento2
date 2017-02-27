<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert that JS validation error message is visible beside every required product option.
 */
class AssertProductCustomOptionsErrors extends AbstractConstraint
{
    /**
     * Assert that JS validation error message is visible beside every required product option.
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
                    $catalogProductView->getCustomOptionsBlock()->validationErrorMessageIsVisible($option['title']),
                    'JS validation error message is absent after required product option ' . $option['title']
                );
            }
        }
    }

    /**
     * Assert success message that JS validation error message is visible beside every required product option.
     *
     * @return string
     */
    public function toString()
    {
        return 'JS validation error message is visible beside every required product option.';
    }
}
