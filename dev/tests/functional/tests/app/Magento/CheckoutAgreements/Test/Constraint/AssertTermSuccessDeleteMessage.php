<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTermSuccessDeleteMessage
 * Check that after deleting Term successful delete message appears.
 */
class AssertTermSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Success terms and conditions delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'You deleted the condition.';

    /**
     * Assert that after deleting Term successful delete message appears.
     *
     * @param CheckoutAgreementIndex $agreementIndex
     * @return void
     */
    public function processAssert(CheckoutAgreementIndex $agreementIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $agreementIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success delete message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Terms and Conditions success delete message is present.';
    }
}
