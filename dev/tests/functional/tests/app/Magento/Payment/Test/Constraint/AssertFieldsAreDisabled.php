<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;

/**
 * Class AssertFieldsAreDisabled
 *
 * Assert that field are inactive.
 */
class AssertFieldsAreDisabled extends AbstractConstraint
{
    /**
     * Assert that field are disabled.
     *
     * @param array $fieldIds
     * @return void
     */
    public function processAssert(SystemConfigEditSectionPayment $configEditSectionPayment, array $fieldIds)
    {
        foreach ($fieldIds as $fieldId) {
            \PHPUnit\Framework\Assert::assertTrue(
                $configEditSectionPayment->getPaymentsConfigBlock()->isFieldDisabled($fieldId),
                'Field is active.'
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
        return 'Field is disabled.';
    }
}
