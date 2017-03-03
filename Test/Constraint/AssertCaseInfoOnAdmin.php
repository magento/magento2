<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Signifyd\Test\Fixture\SignifydData;

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
     * Signifyd data fixture.
     *
     * @var SignifydData
     */
    private $signifydData;

    /**
     * Order id.
     *
     * @var string
     */
    private $orderId;

    /**
     * Assert that Signifyd Case information is correct in Admin.
     *
     * @param SalesOrderView $orderView
     * @param OrderIndex $salesOrder
     * @param SignifydData $signifydData
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $orderView,
        OrderIndex $salesOrder,
        SignifydData $signifydData,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        $this->orderView = $orderView;
        $this->signifydData = $signifydData;
        $this->orderId = $orderId;

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
            $this->signifydData->getCaseStatus(),
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
            $this->signifydData->getGuaranteeDisposition(),
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
            $this->signifydData->getReviewDisposition(),
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
