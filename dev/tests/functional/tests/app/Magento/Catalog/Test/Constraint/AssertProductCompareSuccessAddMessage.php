<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductCompareSuccessAddMessage
 */
class AssertProductCompareSuccessAddMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    const SUCCESS_MESSAGE = 'You added product %s to the comparison list.';

    /**
     * Assert success message is presented on page
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductView $catalogProductView, FixtureInterface $product)
    {
        $successMessage = sprintf(self::SUCCESS_MESSAGE, $product->getName());
        $actualMessage = $catalogProductView->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            $successMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $successMessage
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product has been added compare products list.';
    }
}
