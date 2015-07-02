<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Paypal\Model\Pro
 */
namespace Magento\Paypal\Test\Unit\Model;

class ProTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $args = $objectHelper->getConstructArguments(
            'Magento\Paypal\Model\Pro',
            ['infoFactory' => $this->getMock('Magento\Paypal\Model\InfoFactory', ['create'], [], '', false)]
        );
        /** @var $pro \Magento\Paypal\Model\Pro */
        $this->_pro = $this->getMock('Magento\Paypal\Model\Pro', ['_isPaymentReviewRequired'], $args);
    }

    /**
     * @param bool $pendingReason
     * @param bool $isReviewRequired
     * @param bool $expected
     * @dataProvider canReviewPaymentDataProvider
     */
    public function testCanReviewPayment($pendingReason, $isReviewRequired, $expected)
    {
        $this->_pro->expects(
            $this->any()
        )->method(
            '_isPaymentReviewRequired'
        )->will(
            $this->returnValue($isReviewRequired)
        );
        $payment = $this->getMockBuilder(
            'Magento\Payment\Model\Info'
        )->disableOriginalConstructor()->setMethods(
            ['getAdditionalInformation', '__wakeup']
        )->getMock();
        $payment->expects(
            $this->once()
        )->method(
            'getAdditionalInformation'
        )->with(
            $this->equalTo(\Magento\Paypal\Model\Info::PENDING_REASON_GLOBAL)
        )->will(
            $this->returnValue($pendingReason)
        );

        $this->assertEquals($expected, $this->_pro->canReviewPayment($payment));
    }

    /**
     * @return array
     */
    public function canReviewPaymentDataProvider()
    {
        return [
            [\Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW, true, false],
            [\Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW, false, false],
            ['another_pending_reason', false, false],
            ['another_pending_reason', true, true]
        ];
    }
}
