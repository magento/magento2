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
        $orderView->open(['order_id' => $orderId]);
        $fraudBlock = $orderView->getFraudProtectionBlock();

        \PHPUnit_Framework_Assert::assertEquals(
            self::$caseStatus,
            $fraudBlock->getCaseStatus(),
            'Case status is wrong for order #'.$orderId
        );

        \PHPUnit_Framework_Assert::assertEquals(
            self::$guaranteeDisposition,
            $fraudBlock->getCaseGuaranteeDisposition(),
            'Case Guarantee Disposition status is wrong for order #'.$orderId
        );

        \PHPUnit_Framework_Assert::assertEquals(
            self::$reviewDisposition,
            $fraudBlock->getCaseReviewDisposition(),
            'Case Review Disposition status is wrong for order #'.$orderId
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
