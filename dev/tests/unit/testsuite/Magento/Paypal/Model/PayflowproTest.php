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
 * Test class for \Magento\Paypal\Model\Payflowpro
 */
namespace Magento\Paypal\Model;

class PayflowproTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payflowpro
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configFactory;

    protected function setUp()
    {
        $this->_configFactory = $this->getMock(
            'Magento\Paypal\Model\ConfigFactory',
            ['create', 'getBuildNotationCode'],
            [],
            '',
            false
        );
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $this->_helper->getObject(
            'Magento\Paypal\Model\Payflowpro',
            ['configFactory' => $this->_configFactory]
        );
    }

    /**
     * @param mixed $amountPaid
     * @param string $paymentType
     * @param bool $expected
     * @dataProvider canVoidDataProvider
     */
    public function testCanVoid($amountPaid, $paymentType, $expected)
    {
        $payment = $this->_helper->getObject($paymentType);
        $payment->setAmountPaid($amountPaid);
        $this->assertEquals($expected, $this->_model->canVoid($payment));
    }

    /**
     * @return array
     */
    public function canVoidDataProvider()
    {
        return array(
            array(0, 'Magento\Sales\Model\Order\Invoice', false),
            array(0, 'Magento\Sales\Model\Order\Creditmemo', false),
            array(12.1, 'Magento\Sales\Model\Order\Payment', false),
            array(0, 'Magento\Sales\Model\Order\Payment', true),
            array(null, 'Magento\Sales\Model\Order\Payment', true)
        );
    }

    public function testCanCapturePartial()
    {
        $this->assertTrue($this->_model->canCapturePartial());
    }

    public function testCanRefundPartialPerInvoice()
    {
        $this->assertTrue($this->_model->canRefundPartialPerInvoice());
    }

    /**
     * test for _buildBasicRequest (BDCODE) and catch exception of response
     */
    public function testFetchTransactionInfoForBNException()
    {
        $this->_configFactory->expects($this->once())->method('create')->will($this->returnSelf());
        $this->_configFactory->expects($this->once())->method('getBuildNotationCode')
            ->will($this->returnValue('BNCODE'));
        $payment = $this->getMock('Magento\Payment\Model\Info', [], [], '', false);
        $this->setExpectedException(
            'Magento\Framework\Model\Exception', 'User authentication failed'
        );
        $this->_model->fetchTransactionInfo($payment, 'AD49G8N825');
    }
}
