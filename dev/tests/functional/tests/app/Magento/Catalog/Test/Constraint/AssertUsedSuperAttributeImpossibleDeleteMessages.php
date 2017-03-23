<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUsedSuperAttributeImpossibilityDeleteMessages
 * Assert that it's impossible to delete configurable attribute that is used in created configurable product
 */
class AssertUsedSuperAttributeImpossibleDeleteMessages extends AbstractConstraint
{
    /**
     * Impossible to delete message
     */
    const ERROR_DELETE_MESSAGE = 'This attribute is used in configurable products.';

    /**
     * Assert that it's impossible to delete configurable attribute that is used in created configurable product
     *
     * @param CatalogProductAttributeNew $newPage
     * @return void
     */
    public function processAssert(CatalogProductAttributeNew $newPage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_DELETE_MESSAGE,
            $newPage->getMessagesBlock()->getErrorMessage(),
            'Wrong impossible to delete message is not displayed.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Error delete message is present while deleting assigned configurable attribute.';
    }
}
