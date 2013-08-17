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
 * @category    Magento
 * @package     Magento_Paypal
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Paypal_Model_Pro
 */
class Mage_Paypal_Model_ProTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param bool $pendingReason
     * @param bool $isReviewRequired
     * @param bool $expected
     * @dataProvider canReviewPaymentDataProvider
     */
    public function testCanReviewPayment($pendingReason, $isReviewRequired, $expected)
    {
        /** @var $pro Mage_Paypal_Model_Pro */
        $pro = $this->getMock('Mage_Paypal_Model_Pro', array('_isPaymentReviewRequired'));
        $pro->expects($this->any())
            ->method('_isPaymentReviewRequired')
            ->will($this->returnValue($isReviewRequired));
        $payment = $this->getMockBuilder('Mage_Payment_Model_Info')
            ->disableOriginalConstructor()
            ->setMethods(array('getAdditionalInformation'))
            ->getMock();
        $payment->expects($this->once())
            ->method('getAdditionalInformation')
            ->with($this->equalTo(Mage_Paypal_Model_Info::PENDING_REASON_GLOBAL))
            ->will($this->returnValue($pendingReason));

        $this->assertEquals($expected, $pro->canReviewPayment($payment));
    }

    public function canReviewPaymentDataProvider()
    {
        return array(
            array(Mage_Paypal_Model_Info::PAYMENTSTATUS_REVIEW, true, false),
            array(Mage_Paypal_Model_Info::PAYMENTSTATUS_REVIEW, false, false),
            array('another_pending_reason', false, false),
            array('another_pending_reason', true, true),
        );
    }
}
