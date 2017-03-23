<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleSuccessSaveMessage
 */
class AssertCatalogPriceRuleSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You saved the rule.';

    /**
     * Assert that success message is displayed after Catalog Price Rule saved
     *
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @return void
     */
    public function processAssert(CatalogRuleIndex $pageCatalogRuleIndex)
    {
        $actualMessages = $pageCatalogRuleIndex->getMessagesBlock()->getSuccessMessages();
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
