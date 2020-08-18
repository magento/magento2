<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Invoice\View
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Invoice;

use Magento\Sales\Block\Adminhtml\Order\Invoice\View;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @param bool $canReviewPayment
     * @param bool $canFetchUpdate
     * @param bool $expectedResult
     * @dataProvider isPaymentReviewDataProvider
     */
    public function testIsPaymentReview($canReviewPayment, $canFetchUpdate, $expectedResult)
    {
        // Create order mock
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->any())->method('canReviewPayment')->willReturn($canReviewPayment);
        $order->expects(
            $this->any()
        )->method(
            'canFetchPaymentReviewUpdate'
        )->willReturn(
            $canFetchUpdate
        );

        // Create invoice mock
        $invoice = $this->getMockBuilder(
            Invoice::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getOrder']
            )->getMock();
        $invoice->expects($this->once())->method('getOrder')->willReturn($order);

        // Prepare block to test protected method
        $block = $this->getMockBuilder(
            View::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getInvoice']
            )->getMock();
        $block->expects($this->once())->method('getInvoice')->willReturn($invoice);
        $testMethod = new \ReflectionMethod(
            View::class,
            '_isPaymentReview'
        );
        $testMethod->setAccessible(true);

        $this->assertEquals($expectedResult, $testMethod->invoke($block));
    }

    /**
     * @return array
     */
    public function isPaymentReviewDataProvider()
    {
        return [
            [true, true, true],
            [true, false, true],
            [false, true, true],
            [false, false, false]
        ];
    }
}
