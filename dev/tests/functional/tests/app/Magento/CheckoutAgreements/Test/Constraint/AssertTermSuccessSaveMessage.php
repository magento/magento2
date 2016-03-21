<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTermSuccessSaveMessage
 * Check that after save block successful message appears.
 */
class AssertTermSuccessSaveMessage extends AbstractConstraint
{
    /**
     * Success terms and conditions save message
     */
    const SUCCESS_SAVE_MESSAGE = 'You saved the condition.';

    /**
     * Assert that after save block successful message appears.
     *
     * @param CheckoutAgreementIndex $agreementIndex
     * @return void
     */
    public function processAssert(CheckoutAgreementIndex $agreementIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_SAVE_MESSAGE,
            $agreementIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Terms and Conditions success create message is present.';
    }
}
