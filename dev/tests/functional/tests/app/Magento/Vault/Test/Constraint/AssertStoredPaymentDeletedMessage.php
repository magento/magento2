<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Constraint;

use Magento\Vault\Test\Page\StoredPaymentMethods;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message is correct.
 */
class AssertStoredPaymentDeletedMessage extends AbstractConstraint
{
    /**
     * Message of success deletion of stored payment method.
     */
    const SUCCESS_MESSAGE = 'Stored Payment Method was successfully removed';

    /**
     * Assert that message of success deletion of stored payment method is present.
     *
     * @param StoredPaymentMethods $storedPaymentMethods
     */
    public function processAssert(StoredPaymentMethods $storedPaymentMethods)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $storedPaymentMethods->getMessagesBlock()->getSuccessMessage(),
            'Message of success deletion of stored payment method is not present or wrong.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message on Store Payment Methods page is correct.';
    }
}
