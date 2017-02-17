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
class AssertCaseInfo extends AbstractConstraint
{
    /**
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * @param SignifydCases $signifydCases
     * @param Address $billingAddress
     * @param array $prices
     * @param string $orderId
     * @param string $customerFullName
     * @return void
     */
    public function processAssert(
        SignifydCases $signifydCases,
        Address $billingAddress,
        array $prices,
        $orderId,
        $customerFullName
    ) {
        $this->signifydCases = $signifydCases;

        $this->checkGuaranteeDisposition();
        $this->checkCvvResponse();
        $this->checkAvsResponse();
        $this->checkBillingAddress($billingAddress);
        $this->checkOrderAmount($prices);
        $this->checkOrderId($orderId);
        $this->checkCardHolder($customerFullName);
    }

    /**
     * Checks guarantee disposition match
     *
     * @return void
     */
    private function checkGuaranteeDisposition()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            'Approved',
            $this->signifydCases->getCaseInfoBlock()->getGuaranteeDisposition(),
            'Guarantee disposition in Signifyd sandbox not match.'
        );
    }

    /**
     * Checks CVV response match
     *
     * @return void
     */
    private function checkCvvResponse()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            'CVV2 Match (M)',
            $this->signifydCases->getCaseInfoBlock()->getCvvResponse(),
            'CVV response in Signifyd sandbox not match.'
        );
    }

    /**
     * Checks AVS response match
     *
     * @return void
     */
    private function checkAvsResponse()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            'Full match (Y)',
            $this->signifydCases->getCaseInfoBlock()->getAvsResponse(),
            'AVS response in Signifyd sandbox not match.'
        );
    }

    /**
     * Checks billing address match
     *
     * @param Address $billingAddress
     * @return void
     */
    private function checkBillingAddress(Address $billingAddress)
    {
        \PHPUnit_Framework_Assert::assertContains(
            $billingAddress->getStreet(),
            $this->signifydCases->getCaseInfoBlock()->getBillingAddress(),
            'Billing address in Signifyd sandbox not match.'
        );
    }

    /**
     * Checks order amount match
     *
     * @param array $prices
     * @return void
     */
    private function checkOrderAmount(array $prices)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            number_format($prices['grandTotal'], 2),
            $this->signifydCases->getCaseInfoBlock()->getOrderAmount(),
            'Order amount in Signifyd sandbox not match.'
        );
    }

    /**
     * Checks order id match
     *
     * @param string $orderId
     * @return void
     */
    private function checkOrderId($orderId)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $orderId,
            $this->signifydCases->getCaseInfoBlock()->getOrderId(),
            'Order id in Signifyd sandbox not match.'
        );
    }

    /**
     * Checks card holder match
     *
     * @param string $customerFullName
     * @return void
     */
    private function checkCardHolder($customerFullName)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $customerFullName,
            $this->signifydCases->getCaseInfoBlock()->getCardHolder(),
            'Card holder in Signifyd sandbox not match.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Signifyd case information in sandbox is correct.';
    }
}
