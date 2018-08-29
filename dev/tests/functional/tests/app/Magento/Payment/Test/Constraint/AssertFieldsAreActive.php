<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;

/**
 * Class AssertFieldsAreActive
 *
 * Assert that fields are active.
 */
class AssertFieldsAreActive extends AbstractConstraint
{
    /**
     * Assert that fields are active.
     *
     * @param array $fieldIds
     * @return void
     */
    public function processAssert(SystemConfigEditSectionPayment $configEditSectionPayment, array $fieldIds)
    {
        foreach ($fieldIds as $fieldId) {
            \PHPUnit\Framework\Assert::assertFalse(
                $configEditSectionPayment->getPaymentsConfigBlock()->isFieldDisabled($fieldId),
                'Field is disabled.'
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
        return 'Field is active.';
    }
}
