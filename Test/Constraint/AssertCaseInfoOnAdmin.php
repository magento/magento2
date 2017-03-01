<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Case Entity is correct on order page in Admin.
 */
class AssertCaseInfoOnAdmin extends AbstractConstraint
{
    /**
     * Customized order view page.
     *
     * @var SalesOrderView
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
     * Assert that Signifyd Case information is correct in Admin.
     *
     * @param SalesOrderView $orderView
     * @param string $orderId
     * @param array $signifydData
     * @return void
     */
    public function processAssert(
        SalesOrderView $orderView,
        $orderId,
        array $signifydData
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
     * Checks case status is correct.
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
     * Checks case guarantee disposition is correct.
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
     * Checks case review disposition is correct.
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
     * @inheritdoc
     */
    public function toString()
    {
        return 'Signifyd Case information is correct in Admin.';
    }
}
