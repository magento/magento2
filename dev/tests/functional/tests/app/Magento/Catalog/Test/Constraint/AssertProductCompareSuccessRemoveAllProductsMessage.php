<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductCompareSuccessRemoveAllProductsMessage
 */
class AssertProductCompareSuccessRemoveAllProductsMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You cleared the comparison list.';

    /**
     * Assert message is appeared on "Compare Products" page.
     *
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(CatalogProductView $catalogProductView)
    {
        $actualMessage = $catalogProductView->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Compare Product success message is present.';
    }
}
