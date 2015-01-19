<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Sales\Test\Page\SalesGuestPrint;
use Mtf\Constraint\AbstractConstraint;

/**
 * Assert that BillingAddress printed correctly on sales guest print page.
 */
class AssertSalesPrintOrderBillingAddress extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that BillingAddress printed correctly on sales guest print page.
     *
     * @param SalesGuestPrint $salesGuestPrint
     * @param AddressInjectable $billingAddress
     * @return void
     */
    public function processAssert(SalesGuestPrint $salesGuestPrint, AddressInjectable $billingAddress)
    {
        $addressRenderer = $this->objectManager->create(
            'Magento\Customer\Test\Block\Address\Renderer',
            ['address' => $billingAddress, 'type' => 'html']
        );
        $expectedBillingAddress = $addressRenderer->render();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedBillingAddress,
            $salesGuestPrint->getInfoBlock()->getBillingAddress(),
            "Billing address was printed incorrectly."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Billing address printed correctly.";
    }
}
