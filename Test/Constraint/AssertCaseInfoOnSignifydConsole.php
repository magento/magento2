<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;

/**
 * Assert that order information is correct on Signifyd case info page.
 */
class AssertCaseInfoOnSignifydConsole extends AbstractConstraint
{
    /**
     * Signifyd cases page.
     *
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * @param SignifydCases $signifydCases
     * @param Address $billingAddress
     * @param array $prices
     * @param string $orderId
     * @param string $customerFullName
     * @param array $signifydData
     * @return void
     */
    public function processAssert(
        SignifydCases $signifydCases,
        Address $billingAddress,
        array $prices,
        $orderId,
        $customerFullName,
        $signifydData
    ) {
        $this->signifydCases = $signifydCases;

        $this->checkGuaranteeDisposition($signifydData['guaranteeDisposition']);
        $this->checkCvvResponse($signifydData['cvvResponse']);
        $this->checkAvsResponse($signifydData['avsResponse']);
        $this->checkOrderId($orderId);
        $this->checkOrderAmount($prices['grandTotal']);
        $this->checkCardHolder($customerFullName);
        $this->checkBillingAddress($billingAddress);
    }

    /**
     * Checks that guarantee disposition matches.
     *
     * @param string $guaranteeDisposition
     * @return void
     */
    private function checkGuaranteeDisposition($guaranteeDisposition)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $guaranteeDisposition,
            $this->signifydCases->getCaseInfoBlock()->getGuaranteeDisposition(),
            'Guarantee disposition is incorrect in Signifyd console.'
        );
    }

    /**
     * Checks that CVV response matches.
     *
     * @param string $cvvResponse
     * @return void
     */
    private function checkCvvResponse($cvvResponse)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $cvvResponse,
            $this->signifydCases->getCaseInfoBlock()->getCvvResponse(),
            'CVV response is incorrect in Signifyd console.'
        );
    }

    /**
     * Checks that AVS response matches.
     *
     * @param string $avsResponse
     * @return void
     */
    private function checkAvsResponse($avsResponse)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $avsResponse,
            $this->signifydCases->getCaseInfoBlock()->getAvsResponse(),
            'AVS response is incorrect in Signifyd console.'
        );
    }

    /**
     * Checks that order id matches.
     *
     * @param string $orderId
     * @return void
     */
    private function checkOrderId($orderId)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $orderId,
            $this->signifydCases->getCaseInfoBlock()->getOrderId(),
            'Order id is incorrect in Signifyd console.'
        );
    }

    /**
     * Checks that order amount matches.
     *
     * @param string $amount
     * @return void
     */
    private function checkOrderAmount($amount)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            number_format($amount, 2),
            $this->signifydCases->getCaseInfoBlock()->getOrderAmount(),
            'Order amount is incorrect in Signifyd console.'
        );
    }

    /**
     * Checks that card holder matches.
     *
     * @param string $customerFullName
     * @return void
     */
    private function checkCardHolder($customerFullName)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $customerFullName,
            $this->signifydCases->getCaseInfoBlock()->getCardHolder(),
            'Card holder name is incorrect in Signifyd console.'
        );
    }

    /**
     * Checks that billing address matches.
     *
     * @param Address $billingAddress
     * @return void
     */
    private function checkBillingAddress(Address $billingAddress)
    {
        \PHPUnit_Framework_Assert::assertContains(
            $billingAddress->getStreet(),
            $this->signifydCases->getCaseInfoBlock()->getBillingAddress(),
            'Billing address is incorrect in Signifyd console.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Case information is correct in Signifyd console.';
    }
}
