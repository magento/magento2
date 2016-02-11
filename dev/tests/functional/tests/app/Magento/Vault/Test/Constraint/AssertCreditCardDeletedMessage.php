<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\Constraint;

use Magento\Vault\Test\Page\MyCreditCards;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCreditCardDeletedMessage
 * Assert that success message is correct
 */
class AssertCreditCardDeletedMessage extends AbstractConstraint
{
    /**
     * Message of success deletion of credit card.
     */
    const SUCCESS_MESSAGE = 'Credit Card was successfully removed';

    /**
     * Assert that success message is correct.
     *
     * @param MyCreditCards $myCreditCards
     */
    public function processAssert(MyCreditCards $myCreditCards)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $myCreditCards->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message on My Credit Cards page is correct.';
    }
}
