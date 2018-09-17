<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Constraint;

use Magento\Braintree\Test\Page\Adminhtml\BraintreeSettlementReportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Class AssertTransactionIsPresentInSettlementReport
 */
class AssertTransactionIsPresentInSettlementReport extends AbstractConstraint
{
    /**
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * @var BraintreeSettlementReportIndex
     */
    private $settlementReportIndex;

    /**
     * @param $orderId
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param BraintreeSettlementReportIndex $braintreeSettlementReportIndex
     * @throws \Exception
     */
    public function processAssert(
        $orderId,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        BraintreeSettlementReportIndex $braintreeSettlementReportIndex
    ) {
        $this->salesOrderView = $salesOrderView;
        $this->settlementReportIndex = $braintreeSettlementReportIndex;

        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        $transactionId = $this->getTransactionId();
        \PHPUnit_Framework_Assert::assertNotEmpty($transactionId);

        $this->settlementReportIndex->open();

        $grid = $this->settlementReportIndex->getSettlementReportGrid();
        $grid->search(['id' => $transactionId]);

        $ids = $grid->getTransactionIds();

        \PHPUnit_Framework_Assert::assertTrue(in_array($transactionId, $ids));
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Transaction is present in settlement report.';
    }

    /**
     * Get transaction id from order comments
     * @return mixed
     */
    private function getTransactionId()
    {
        $comments = $this->salesOrderView->getOrderHistoryBlock()->getCommentsHistory();
        $transactionId = null;

        preg_match('/(\w+-*\w+)"/', $comments, $matches);
        if (!empty($matches[1])) {
            $transactionId = $matches[1];
        }

        return $transactionId;
    }
}
