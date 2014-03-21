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
namespace Magento\Paypal\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentDataMock;

    /**
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_helper;

    public function setUp()
    {
        $this->_paymentDataMock = $this->getMockBuilder(
            'Magento\Payment\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getStoreMethods', 'getPaymentMethods')
        )->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            'Magento\Paypal\Helper\Data',
            array('paymentData' => $this->_paymentDataMock)
        );
    }

    /**
     * @dataProvider getBillingAgreementMethodsDataProvider
     * @param $store
     * @param $quote
     * @param $paymentMethods
     * @param $expectedResult
     */
    public function testGetBillingAgreementMethods($store, $quote, $paymentMethods, $expectedResult)
    {
        $this->_paymentDataMock->expects(
            $this->any()
        )->method(
            'getStoreMethods'
        )->with(
            $store,
            $quote
        )->will(
            $this->returnValue($paymentMethods)
        );
        $this->assertEquals($expectedResult, $this->_helper->getBillingAgreementMethods($store, $quote));
    }

    /**
     * @dataProvider canManageBillingAgreementsDataProvider
     * @param $expectedResult
     * @param $methodInstance
     */
    public function testCanManageBillingAgreements($expectedResult, $methodInstance)
    {
        $this->assertEquals($expectedResult, $this->_helper->canManageBillingAgreements($methodInstance));
    }

    /**
     * @return array
     */
    public function getBillingAgreementMethodsDataProvider()
    {
        $quoteMock = $this->getMockBuilder(
            'Magento\Sales\Model\Quote'
        )->disableOriginalConstructor()->setMethods(
            null
        );
        $methodInterfaceMock = $this->getMockBuilder(
            'Magento\Paypal\Model\Billing\Agreement\MethodInterface'
        )->getMock();

        return array(
            array('1', $quoteMock, array($methodInterfaceMock), array($methodInterfaceMock)),
            array('1', $quoteMock, array(new \StdClass()), array())
        );
    }

    /**
     * @return array
     */
    public function canManageBillingAgreementsDataProvider()
    {
        $methodInterfaceMock = $this->getMockBuilder(
            'Magento\Paypal\Model\Billing\Agreement\MethodInterface'
        )->getMock();
        return array(array(true, $methodInterfaceMock), array(false, new \StdClass()));
    }
}
