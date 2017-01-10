<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after save block successful message appears.
 */
class AssertSynonymMergeErrorMessage extends AbstractConstraint
{
    const ERROR_MESSAGE = 'The terms you entered';

    /**
     * Assert that after save Synonym Group successful message appears.
     *
     * @param SynonymGroupNew $synonymGroupNew
     * @return void
     */
    public function processAssert(SynonymGroupNew $synonymGroupNew)
    {
        $actualMessage = $synonymGroupNew->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertContains(
            self::ERROR_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE
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
        return 'Synonym Group error message is present.';
    }
}
