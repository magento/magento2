<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountCreate;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerSuccessRegisterMessage
 *
 */
class AssertCustomerSuccessRegisterMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'Thank you for registering with Main Website Store.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that success message is displayed after customer registered on frontend
     *
     * @param CustomerAccountCreate $registerPage
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage)
    {
        $actualMessage = $registerPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of success register message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return "Customer is successfully registered.";
    }
}
