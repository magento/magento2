<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Signifyd\Test\Page\Adminhtml\OrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Case Entity is correct on order page in backend.
 */
class AssertCaseInfoOnBackend extends AbstractConstraint
{
    /**
     * Customized order view page.
     *
     * @var OrderView
     */
    private $orderView;

    /**
     * Order id.
     *
     * @var string
     */
    private $orderId;

    /**
     * Array of Signifyd config data.
     *
     * @var array
     */
    private $signifydData;

    /**
     * Assert that Signifyd Case information is correct on backend.
     *
     * @param OrderView $orderView
     * @param string $orderId
     * @param array $signifydData
     * @return void
     */
    public function processAssert(
        OrderView $orderView,
        $orderId,
        $signifydData
    ) {
        $this->orderView = $orderView;
        $this->orderView->open(['order_id' => $orderId]);
        $this->orderId = $orderId;
        $this->signifydData = $signifydData;

        $this->checkCaseStatus();
        $this->checkCaseGuaranteeDisposition();
        $this->checkCaseReviewDisposition();
    }

    /**
     * Checks that case status matches.
     *
     * @return void
     */
    private function checkCaseStatus()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $this->signifydData['caseStatus'],
            $this->orderView->getFraudProtectionBlock()->getCaseStatus(),
            'Case status is wrong for order #' . $this->orderId
        );
    }

    /**
     * Checks that case guarantee disposition matches.
     *
     * @return void
     */
    private function checkCaseGuaranteeDisposition()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $this->signifydData['guaranteeDisposition'],
            $this->orderView->getFraudProtectionBlock()->getCaseGuaranteeDisposition(),
            'Case Guarantee Disposition status is wrong for order #' . $this->orderId
        );
    }

    /**
     * Checks that case review disposition matches.
     *
     * @return void
     */
    private function checkCaseReviewDisposition()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $this->signifydData['reviewDisposition'],
            $this->orderView->getFraudProtectionBlock()->getCaseReviewDisposition(),
            'Case Review Disposition status is wrong for order #' . $this->orderId
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Signifyd Case information is correct on backend.';
    }
}
