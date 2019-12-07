<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\AdvancedResult;

/**
 * Advanced Search without results.
 */
class AssertAdvancedSearchNoResult extends AbstractConstraint
{
    /**
     * Text for error messages.
     */
    const ERROR_MESSAGE = 'We can\'t find any items matching these search criteria. Modify your search.';

    /**
     * Assert that Advanced Search without results.
     *
     * @param AdvancedResult $resultPage
     * @return void
     */
    public function processAssert(AdvancedResult $resultPage)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $resultPage->getSearchResultBlock()->isVisibleMessages(self::ERROR_MESSAGE),
            "The error message '" . self::ERROR_MESSAGE . "' is not visible."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Error message is visible.';
    }
}
