<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWrongPassConfirmationMessage
 * Check that conformation message is present
 */
class AssertWrongPassConfirmationMessage extends AbstractConstraint
{
    /**
     * Conformation message
     */
    const CONFIRMATION_MESSAGE = 'Confirm your new password';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that conformation message is present
     *
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function processAssert(CustomerAccountEdit $customerAccountEdit)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::CONFIRMATION_MESSAGE,
            $customerAccountEdit->getMessages()->getErrorMessages()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Conformation message is displayed.';
    }
}
