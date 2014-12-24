<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAddressDeletedFrontend
 * Assert that deleted customers address is absent in Address Book in Customer Account
 */
class AssertAddressDeletedFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Expected message
     */
    const EXPECTED_MESSAGE = 'You have no additional address entries in your address book.';

    /**
     * Asserts that Asserts that 'Additional Address Entries' contains expected message
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex)
    {
        $customerAccountIndex->open();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');
        $actualText = $customerAccountIndex->getAdditionalAddressBlock()->getBlockText();
        \PHPUnit_Framework_Assert::assertTrue(
            self::EXPECTED_MESSAGE == $actualText,
            'Expected text is absent in Additional Address block.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Deleted address is absent in "Additional Address Entries" block.';
    }
}
