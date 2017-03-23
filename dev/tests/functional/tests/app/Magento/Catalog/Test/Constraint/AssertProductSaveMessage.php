<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductSaveMessage
 */
class AssertProductSaveMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const SUCCESS_MESSAGE = 'You saved the product.';

    /**
     * Assert that success message is displayed after product save.
     *
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(CatalogProductEdit $productPage)
    {
        $actualMessages = $productPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertContains(
            self::SUCCESS_MESSAGE,
            $actualMessages,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual:\n" . implode("\n - ", $actualMessages)
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product success save message is present.';
    }
}
