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
     * @var OrderView
     */
    private $orderView;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private static $caseStatus = 'Open';

    /**
     * @var string
     */
    private static $guaranteeDisposition = 'Approved';

    /**
     * @var string
     */
    private static $reviewDisposition = 'Good';

    /**
     * Assert that Signifyd Case information is correct on backend.
     *
     * @param OrderView $orderView
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        OrderView $orderView,
        $orderId
    ) {
        $this->orderView = $orderView;
        $this->orderView->open(['order_id' => $orderId]);
        $this->orderId = $orderId;

        $this->checkCaseStatus();
        $this->checkCaseGuaranteeDisposition();
        $this->checkCaseReviewDisposition();
    }

    /**
     * Checks case status match
     *
     * @return void
     */
    private function checkCaseStatus()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::$caseStatus,
            $this->orderView->getFraudProtectionBlock()->getCaseStatus(),
            'Case status is wrong for order #' . $this->orderId
        );
    }

    /**
     * Checks case guarantee disposition match
     *
     * @return void
     */
    private function checkCaseGuaranteeDisposition()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::$guaranteeDisposition,
            $this->orderView->getFraudProtectionBlock()->getCaseGuaranteeDisposition(),
            'Case Guarantee Disposition status is wrong for order #' . $this->orderId
        );
    }

    /**
     * Checks case review disposition match
     *
     * @return void
     */
    private function checkCaseReviewDisposition()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::$reviewDisposition,
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
