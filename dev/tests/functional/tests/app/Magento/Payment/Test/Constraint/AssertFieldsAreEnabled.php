<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;

/**
 * Class AssertFieldsAreEnabled
 *
 * Assert that fields are enabled.
 */
class AssertFieldsAreEnabled extends AbstractConstraint
{
    /**
     * Assert that field is present.
     *
     * @param array $fieldIds
     * @return void
     */
    public function processAssert(SystemConfigEditSectionPayment $configEditSectionPayment, array $fieldIds)
    {
        foreach ($fieldIds as $fieldId) {
            \PHPUnit_Framework_Assert::assertTrue(
                $configEditSectionPayment->getPaymentsConfigBlock()->isFieldEnabled($fieldId),
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
