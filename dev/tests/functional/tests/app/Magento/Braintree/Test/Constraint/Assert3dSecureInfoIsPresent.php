<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Assert that 3D Secure information is present on order page in Admin.
 */
class Assert3dSecureInfoIsPresent extends AbstractConstraint
{
    /**
     * Assert that 3D Secure information is present on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param array $paymentInformation
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView, array $paymentInformation)
    {
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $actualPaymentInformation = $infoTab->getPaymentInfoBlock()->getData();
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
