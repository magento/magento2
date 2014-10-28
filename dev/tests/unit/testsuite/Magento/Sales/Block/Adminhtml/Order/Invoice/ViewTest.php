<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Invoice\View
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice;

class ViewTest extends \PHPUnit_Framework_TestCase
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
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')->disableOriginalConstructor()->getMock();
        $order->expects($this->any())->method('canReviewPayment')->will($this->returnValue($canReviewPayment));
        $order->expects(
            $this->any()
        )->method(
            'canFetchPaymentReviewUpdate'
        )->will(
            $this->returnValue($canFetchUpdate)
        );

        // Create invoice mock
        $invoice = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Invoice'
        )->disableOriginalConstructor()->setMethods(
            array('getOrder', '__wakeup')
        )->getMock();
        $invoice->expects($this->once())->method('getOrder')->will($this->returnValue($order));

        // Prepare block to test protected method
        $block = $this->getMockBuilder(
            'Magento\Sales\Block\Adminhtml\Order\Invoice\View'
        )->disableOriginalConstructor()->setMethods(
            array('getInvoice')
        )->getMock();
        $block->expects($this->once())->method('getInvoice')->will($this->returnValue($invoice));
        $testMethod = new \ReflectionMethod('Magento\Sales\Block\Adminhtml\Order\Invoice\View', '_isPaymentReview');
        $testMethod->setAccessible(true);

        $this->assertEquals($expectedResult, $testMethod->invoke($block));
    }

    public function isPaymentReviewDataProvider()
    {
        return array(
            array(true, true, true),
            array(true, false, true),
            array(false, true, true),
            array(false, false, false)
        );
    }
}
