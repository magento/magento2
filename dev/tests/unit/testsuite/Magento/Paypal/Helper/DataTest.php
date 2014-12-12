<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            ['getStoreMethods', 'getPaymentMethods']
        )->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            'Magento\Paypal\Helper\Data',
            ['paymentData' => $this->_paymentDataMock]
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

        return [
            ['1', $quoteMock, [$methodInterfaceMock], [$methodInterfaceMock]],
            ['1', $quoteMock, [new \StdClass()], []]
        ];
    }
}
