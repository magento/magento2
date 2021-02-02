<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private static $htmlTransactionId =
        '<a target="_blank" href="https://www%1$s.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%2$s">%2$s</a>';

    /**
     * @var string
     */
    private static $txnId = 'XXX123123XXX';

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var \Magento\Paypal\Model\Config | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->paymentMethodList = $this->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            \Magento\Payment\Model\Method\InstanceFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->configMock = $this->getMockBuilder(\Magento\Paypal\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configMockFactory = $this->getMockBuilder(\Magento\Paypal\Model\ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configMockFactory->expects($this->any())->method('create')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('setMethod')->willReturnSelf();

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

        $abstractMethodInstanceMock = $this->getMockBuilder(
            \Magento\Payment\Model\Method\Cc::class
        )->disableOriginalConstructor()->getMock();

        $adapterMethodInstanceMock = $this->getMockBuilder(
            \Magento\Payment\Model\Method\Adapter::class
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
                    [$methodMock, $abstractMethodInstanceMock]
                ],
                []
            ],
            [
                '1',
                $quoteMock,
                [
                    [$methodMock, $adapterMethodInstanceMock]
                ],
                []
            ]
        ];
    }

    /**
     * Sandbox mode
     * Expected link <a target="_blank" href="https://www.sandbox.paypal.com/...</a>
     *
     * @param string $methodCode
     * @dataProvider getHtmlTransactionIdProvider
     */
    public function testGetHtmlTransactionSandboxLink($methodCode)
    {
        $expectedLink = sprintf(self::$htmlTransactionId, '.sandbox', self::$txnId);

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('sandboxFlag')
            ->willReturn(true);

        $this->assertEquals(
            $expectedLink,
            $this->_helper->getHtmlTransactionId($methodCode, self::$txnId)
        );
    }

    /**
     * Real mode
     * Expected link <a target="_blank" href="https://www.paypal.com/...  </a>
     *
     * @param string $methodCode
     * @dataProvider getHtmlTransactionIdProvider
     */
    public function testGetHtmlTransactionRealLink($methodCode)
    {
        $expectedLink = sprintf(self::$htmlTransactionId, '', self::$txnId);

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('sandboxFlag')
            ->willReturn(false);

        $this->assertEquals(
            $expectedLink,
            $this->_helper->getHtmlTransactionId($methodCode, self::$txnId)
        );
    }

    /**
     * @return array
     */
    public function getHtmlTransactionIdProvider()
    {
        return [
            ['paypal_express'],
            ['hosted_pro']
        ];
    }

    /**
     * Invokes with method not in payment list
     * Expected result just returned txtId: "XXX123123XXX"
     */
    public function testGetHtmlTransactionMethodNotInPaymentList()
    {
        $methodCode = 'payflow_express';

        $this->assertEquals(self::$txnId, $this->_helper->getHtmlTransactionId($methodCode, self::$txnId));
    }
}
