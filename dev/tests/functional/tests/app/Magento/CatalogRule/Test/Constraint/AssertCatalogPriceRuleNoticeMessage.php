<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleNoticeMessage
 */
class AssertCatalogPriceRuleNoticeMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const NOTICE_MESSAGE_RULES = 'There are rules that have been changed but were not applied.';
    const NOTICE_MESSAGE_APPLY = ' Please, click Apply Rules in order to see immediate effect in the catalog.';

    /**
     * Assert that message "There are rules that have been changed but were not applied..."
     * is present on page after Save (without applying Rule)
     * or after Edit (without applying Rule) action on the Catalog Price Rules page.
     *
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @return void
     */
    public function processAssert(
        CatalogRuleIndex $pageCatalogRuleIndex
    ) {
        $actualMessage = $pageCatalogRuleIndex->getMessagesBlock()->getNoticeMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOTICE_MESSAGE_RULES . self::NOTICE_MESSAGE_APPLY,
            $actualMessage,
            'Wrong notice message is displayed.'
            . "\nExpected: " . self::NOTICE_MESSAGE_RULES . self::NOTICE_MESSAGE_APPLY
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text notice message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that notice message is displayed';
    }
}
