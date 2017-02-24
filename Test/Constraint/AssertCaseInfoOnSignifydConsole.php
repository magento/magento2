<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Signifyd\Test\Fixture\SignifydAddress;
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
     * @param SignifydAddress $billingAddress
     * @param array $prices
     * @param string $orderId
     * @param string $customerFullName
     * @param array $signifydData
     * @return void
     */
    public function processAssert(
        SignifydCases $signifydCases,
        SignifydAddress $billingAddress,
        array $prices,
        $orderId,
        $customerFullName,
        array $signifydData
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
     * Checks guarantee disposition is correct.
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
     * Checks CVV response is correct.
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
     * Checks AVS response is correct.
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
     * Checks order id is correct.
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
     * Checks order amount is correct.
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
     * Checks card holder is correct.
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
     * Checks billing address is correct.
     *
     * @param SignifydAddress $billingAddress
     * @return void
     */
    private function checkBillingAddress(SignifydAddress $billingAddress)
    {
        \PHPUnit_Framework_Assert::assertContains(
            $billingAddress->getStreet(),
            $this->signifydCases->getCaseInfoBlock()->getBillingAddress(),
            'Billing address is incorrect in Signifyd console.'
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Case information is correct in Signifyd console.';
    }
}
