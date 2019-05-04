<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert required fields on customer form.
 */
class AssertCustomerBackendRequiredFields extends AbstractConstraint
{
    /**
     * Expected message.
     */
    const REQUIRE_MESSAGE = 'This is a required field.';

    /**
     * Assert required fields on customer form.
     *
     * @param CustomerIndexNew $customerNewPage
     * @param array $expectedRequiredFields
     * @return void
     */
    public function processAssert(CustomerIndexNew $customerNewPage, array $expectedRequiredFields)
    {
        $actualRequiredFields = $customerNewPage->getCustomerForm()->getJsErrors();
        foreach ($expectedRequiredFields as $field) {
            \PHPUnit\Framework\Assert::assertTrue(
                isset($actualRequiredFields[$field]),
                "Field '$field' is not highlighted with an JS error."
            );
            \PHPUnit\Framework\Assert::assertEquals(
                self::REQUIRE_MESSAGE,
                $actualRequiredFields[$field],
                "Field '$field' is not highlighted as required."
            );
        }
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All required fields on customer form are highlighted.';
    }
}
