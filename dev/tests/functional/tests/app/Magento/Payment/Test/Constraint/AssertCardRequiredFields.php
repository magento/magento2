<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Payment\Test\Repository\CreditCard;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Class AssertCardRequiredFields
 *
 * Assert that fields are active.
 */
class AssertCardRequiredFields extends AbstractConstraint
{
    /**
     * Expected required field message.
     */
    const REQUIRE_MESSAGE = 'This is a required field.';

    /**
     * Expected required valid number message.
     */
    const VALID_NUMBER_MESSAGE = 'Please enter a valid number in this field.';

    /**
     * Assert required fields on credit card payment method in backend.
     * @param OrderCreateIndex $orderCreateIndex
     * @param CreditCard $creditCard
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex, CreditCard $creditCard)
    {
        $actualRequiredFields = $orderCreateIndex->getCreateBlock()->getBillingMethodBlock()
            ->getJsErrors();
        $creditCardEmpty = $creditCard->get('visa_empty');
        foreach (array_keys($creditCardEmpty) as $field) {
            \PHPUnit\Framework\Assert::assertTrue(
                isset($actualRequiredFields[$field]),
                "Field '$field' is not highlighted with an JS error."
            );
            $expected = self::REQUIRE_MESSAGE;
            if (in_array($field, ['cc_number', 'cc_cid'])) {
                $expected = self::VALID_NUMBER_MESSAGE;
            }
            \PHPUnit\Framework\Assert::assertEquals(
                $expected,
                $actualRequiredFields[$field],
                "Field '$field' is not highlighted as required."
            );
        }
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'All required fields on customer form are highlighted.';
    }
}
