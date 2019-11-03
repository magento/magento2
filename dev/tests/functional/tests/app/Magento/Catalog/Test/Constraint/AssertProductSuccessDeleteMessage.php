<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductSuccessDeleteMessage
 */
class AssertProductSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_DELETE_MESSAGE = 'A total of %d record(s) have been deleted.';

    /**
     * Assert that after deleting product success message.
     *
     * @param FixtureInterface|FixtureInterface[] $product
     * @param CatalogProductIndex $productPage
     * @return void
     */
    public function processAssert($product, CatalogProductIndex $productPage)
    {
        $products = is_array($product) ? $product : [$product];
        $deleteMessage = sprintf(self::SUCCESS_DELETE_MESSAGE, count($products));
        $actualMessage = $productPage->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            $deleteMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $deleteMessage
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
        return 'Assertion that products success delete message is present.';
    }
}
