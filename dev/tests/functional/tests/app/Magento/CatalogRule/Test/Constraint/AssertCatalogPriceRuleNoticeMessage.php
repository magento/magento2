<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert notice message after saving catalog price rule.
 */
class AssertCatalogPriceRuleNoticeMessage extends AbstractConstraint
{
    const NOTICE_MESSAGE_RULES = 'We found updated rules that are not applied.';
    const NOTICE_MESSAGE_APPLY = ' Please click "Apply Rules" to update your catalog.';

    /**
     * Assert that message "We found updated rules that are not applied..."
     * is present on page after Save (without applying Rule)
     * or after Edit (without applying Rule) action on the Catalog Price Rules page.
     *
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @return void
     */
    public function processAssert(
        CatalogRuleIndex $pageCatalogRuleIndex
    ) {
        $actualMessage = $pageCatalogRuleIndex->getMessagesBlock()->getNoticeMessage();
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
