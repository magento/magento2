<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleSuccessSaveMessage
 */
class AssertCatalogPriceRuleSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'The rule has been saved.';

    /**
     * Assert that success message is displayed after Catalog Price Rule saved
     *
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @return void
     */
    public function processAssert(CatalogRuleIndex $pageCatalogRuleIndex)
    {
        $actualMessages = $pageCatalogRuleIndex->getMessagesBlock()->getSuccessMessages();
        if (!is_array($actualMessages)) {
            $actualMessages = [$actualMessages];
        }
        \PHPUnit_Framework_Assert::assertContains(
            self::SUCCESS_MESSAGE,
            $actualMessages,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . implode(',', $actualMessages)
        );
    }

    /**
     * Text success save message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that success message is displayed';
    }
}
