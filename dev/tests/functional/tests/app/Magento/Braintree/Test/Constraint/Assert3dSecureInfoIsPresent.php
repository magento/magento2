<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

class Assert3dSecureInfoIsPresent extends AbstractConstraint
{
    /**
     * Assert that 3D Secure information is  present on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param array $paymentInformation
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView, array $paymentInformation)
    {
        $actualPaymentInformation = $salesOrderView->getBraintreeInfoBlock()->getPaymentInfo();
        foreach ($paymentInformation as $key => $value) {
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                $key,
                $actualPaymentInformation,
                '3D Secure information is not present.'
            );
            \PHPUnit_Framework_Assert::assertEquals(
                $paymentInformation[$key],
                $value,
                '3D Secure information is not equal to information from data set.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return '3D Secure information is present and equals to information from data set.';
    }
}
