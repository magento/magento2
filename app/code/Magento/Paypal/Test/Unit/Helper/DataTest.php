<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\Method\Cc;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Method\Agreement;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
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
     * @var PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var InstanceFactory|MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var Data
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->paymentMethodList = $this->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            InstanceFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configMockFactory = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configMockFactory->expects($this->any())->method('create')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('setMethod')->willReturnSelf();

        $objectManager = new ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            Data::class,
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
            Quote::class
        )->disableOriginalConstructor()
            ->getMock();

        $methodMock = $this->getMockBuilder(
            PaymentMethodInterface::class
        )->getMock();

        $agreementMethodInstanceMock = $this->getMockBuilder(
            Agreement::class
        )->disableOriginalConstructor()
            ->getMock();
        $agreementMethodInstanceMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);

        $abstractMethodInstanceMock = $this->getMockBuilder(
            Cc::class
        )->disableOriginalConstructor()
            ->getMock();

        $adapterMethodInstanceMock = $this->getMockBuilder(
            Adapter::class
        )->disableOriginalConstructor()
            ->getMock();

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
