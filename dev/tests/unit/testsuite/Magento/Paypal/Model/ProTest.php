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
 * Test class for \Magento\Paypal\Model\Pro
 */
namespace Magento\Paypal\Model;

class ProTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $args = $objectHelper->getConstructArguments(
            'Magento\Paypal\Model\Pro',
            array('infoFactory' => $this->getMock('Magento\Paypal\Model\InfoFactory'))
        );
        /** @var $pro \Magento\Paypal\Model\Pro */
        $this->_pro = $this->getMock('Magento\Paypal\Model\Pro', array('_isPaymentReviewRequired'), $args);
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
            array('getAdditionalInformation', '__wakeup')
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
        return array(
            array(\Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW, true, false),
            array(\Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW, false, false),
            array('another_pending_reason', false, false),
            array('another_pending_reason', true, true)
        );
    }
}
