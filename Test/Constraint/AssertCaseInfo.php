<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;

class AssertCaseInfo extends AbstractConstraint
{
    private $cvvResponse = 'CVV2 Match (M)';
    private $avsResponse = 'Full match (Y)';

    public function processAssert(
        SignifydCases $signifydCases,
        Customer $customer,
        OrderInjectable $order,
        Address $billingAddress,
        array $cartPrice
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            $this->cvvResponse,
            $signifydCases->getCaseInfoBlock()->getCvvResponse()
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $this->avsResponse,
            $signifydCases->getCaseInfoBlock()->getAvsResponse()
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $order->getId(),
            $signifydCases->getCaseInfoBlock()->getOrderId()
        );

        \PHPUnit_Framework_Assert::assertEquals(
            number_format($cartPrice['grand_total'], 2),
            $signifydCases->getCaseInfoBlock()->getOrderAmount()
        );

        \PHPUnit_Framework_Assert::assertEquals(
            sprintf('%s %s', $customer->getFirstname(), $customer->getLastname()),
            $signifydCases->getCaseInfoBlock()->getCardHolder()
        );

        \PHPUnit_Framework_Assert::assertContains(
            $billingAddress->getStreet(),
            $signifydCases->getCaseInfoBlock()->getBillingAddress()
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Case information is correct.';
    }
}
