<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var \Magento\Paypal\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->paymentMethodList = $this->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            \Magento\Payment\Model\Method\InstanceFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->configMock = $this->getMock(
            \Magento\Paypal\Model\Config::class,
            [],
            [],
            '',
            false
        );
        $configMockFactory = $this->getMockBuilder(\Magento\Paypal\Model\ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configMockFactory->expects($this->any())->method('create')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('setMethod')->will($this->returnSelf());

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            \Magento\Paypal\Helper\Data::class,
            [
                'methodCodes' => ['expressCheckout' => 'paypal_express', 'hostedPro' => 'hosted_pro'],
                'configFactory' => $configMockFactory
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->_helper,
            'paymentMethodList',
            $this->paymentMethodList
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->_helper,
            'paymentMethodInstanceFactory',
            $this->paymentMethodInstanceFactory
        );
    }

    /**
     * @dataProvider getBillingAgreementMethodsDataProvider
     * @param $store
     * @param $quote
     * @param $paymentMethodsMap
     * @param $expectedResult
     */
    public function testGetBillingAgreementMethods($store, $quote, $paymentMethodsMap, $expectedResult)
    {
        $this->paymentMethodList->expects(static::once())
            ->method('getActiveList')
            ->with($store)
            ->willReturn(array_column($paymentMethodsMap, 0));

        $this->paymentMethodInstanceFactory->expects(static::any())
            ->method('create')
            ->willReturnMap($paymentMethodsMap);

        $this->assertEquals($expectedResult, $this->_helper->getBillingAgreementMethods($store, $quote));
    }

    /**
     * @return array
     */
    public function getBillingAgreementMethodsDataProvider()
    {
        $quoteMock = $this->getMockBuilder(
            \Magento\Quote\Model\Quote::class
        )->disableOriginalConstructor()->getMock();

        $methodMock = $this->getMockBuilder(
            \Magento\Payment\Api\Data\PaymentMethodInterface::class
        )->getMock();

        $agreementMethodInstanceMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Method\Agreement::class
        )->disableOriginalConstructor()->getMock();
        $agreementMethodInstanceMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);

        $methodInstanceMock = $this->getMockBuilder(
            \Magento\Payment\Model\Method\Cc::class
        )->disableOriginalConstructor()->getMock();

        return [
            [
                '1',
                $quoteMock,
                [
                    [$methodMock, $agreementMethodInstanceMock]
                ],
                [$agreementMethodInstanceMock]
            ],
            [
                '1',
                $quoteMock,
                [
                    [$methodMock, $methodInstanceMock]
                ],
                []
            ]
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
