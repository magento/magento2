<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentDataMock;

    /**
     * @var \Magento\Paypal\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

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

        $this->configMock = $this->getMock(
            'Magento\Paypal\Model\Config',
            [],
            [],
            '',
            false
        );
        $configMockFactory = $this->getMockBuilder('Magento\Paypal\Model\ConfigFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configMockFactory->expects($this->any())->method('create')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('setMethod')->will($this->returnSelf());

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            'Magento\Paypal\Helper\Data',
            [
                'paymentData' => $this->_paymentDataMock,
                'methodCodes' => ['expressCheckout' => 'paypal_express', 'hostedPro' => 'hosted_pro'],
                'configFactory' => $configMockFactory
            ]
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
            'Magento\Quote\Model\Quote'
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

    /**
     * @param string $methodCode
     * @param string $htmlTransactionId
     * @dataProvider testGetHtmlTransactionIdProvider
     */
    public function testGetHtmlTransactionId($methodCode, $htmlTransactionId)
    {
        $txnId = 'XXX123123XXX';
        $htmlTransactionId = sprintf($htmlTransactionId, 'sandbox', $txnId);

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->with($this->stringContains('sandboxFlag'))
            ->willReturn(true);

        $this->assertEquals($htmlTransactionId, $this->_helper->getHtmlTransactionId($methodCode, $txnId));
    }

    /**
     * @return array
     */
    public function testGetHtmlTransactionIdProvider()
    {
        $htmlTransactionId =
            '<a target="_blank" href="https://www.%1$s.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%2$s">%2$s</a>';
        return [
            ['paypal_express', $htmlTransactionId],
            ['payflow_express', 'XXX123123XXX'],
            ['hosted_pro', $htmlTransactionId]
        ];
    }
}
