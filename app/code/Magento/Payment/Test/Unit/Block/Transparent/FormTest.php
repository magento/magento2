<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block\Transparent;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /**
     * @var FormTesting|MockObject
     */
    private $formMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TransparentInterface|MockObject
     */
    private $methodMock;

    /**
     * @var Session|MockObject
     */
    private $checkoutSessionMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        $context = $objectManagerHelper->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );

        $this->methodMock = $this->getMockBuilder(TransparentInterface::class)
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->formMock = new FormTesting(
            $context,
            $paymentConfigMock,
            $this->checkoutSessionMock
        );
    }

    public function testIsAjaxRequest()
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->with('isAjax')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->assertTrue($this->formMock->isAjaxRequest());
        $this->assertFalse($this->formMock->isAjaxRequest());
    }

    /**
     * @param string $fieldName
     * @param mixed $fieldValue
     * @param mixed $expected
     *
     * @dataProvider getMethodConfigDataDataProvider
     */
    public function testGetMethodConfigData($fieldName, $fieldValue, $expected)
    {
        $this->initializeMethodWithConfigMock([[$fieldName, null, $fieldValue]]);

        $this->formMock->setMethod($this->methodMock);

        $this->assertEquals($expected, $this->formMock->getMethodConfigData($fieldName));
    }

    /**
     * Initializes method mock with config mock
     *
     * @param array $configMap
     */
    private function initializeMethodWithConfigMock(array $configMap = [])
    {
        $configInterface = $this->getMockBuilder(ConfigInterface::class)
            ->getMock();

        $configInterface->expects($this->any())
            ->method('getValue')
            ->willReturnMap($configMap);

        $this->methodMock->expects($this->any())
            ->method('getConfigInterface')
            ->willReturn($configInterface);
    }

    /**
     * Data provider for testGetMethodConfigData
     *
     * @return array
     * @see testGetMethodConfigData
     *
     * @case #1 Set string value
     * @case #2 Set boolean value
     *
     */
    public function getMethodConfigDataDataProvider()
    {
        return [
            ['gateway_name', 'payment_gateway', 'payment_gateway'],
            ['sandbox_flag', true, true],
        ];
    }

    /**
     * @dataProvider getCgiUrlDataProvider
     *
     * @param $sandboxFlag
     * @param $cgiUrlTestMode
     * @param $cgiUrl
     * @param $expectedUrl
     */
    public function testGetCgiUrl($sandboxFlag, $cgiUrlTestMode, $cgiUrl, $expectedUrl)
    {
        $this->initializeMethodWithConfigMock(
            [
                ['sandbox_flag', null, $sandboxFlag],
                ['cgi_url_test_mode', null, $cgiUrlTestMode],
                ['cgi_url', null, $cgiUrl]
            ]
        );

        $this->formMock->setMethod($this->methodMock);

        $this->assertEquals($expectedUrl, $this->formMock->getCgiUrl());
    }

    /**
     * Data provider for testGetCgiUrl
     *
     * @return array
     * @see testGetCgiUrl
     *
     * @case #1 The sandboxFlag is 1 we expected cgi_url_test_mode_value
     * @case #2 The sandboxFlag is 0 we expected cgi_url_value
     *
     */
    public function getCgiUrlDataProvider()
    {
        return [
            [
                1,
                'cgi_url_test_mode_value',
                'cgi_url_value',
                'cgi_url_test_mode_value'
            ],
            [
                0,
                'cgi_url_test_mode_value',
                'cgi_url_value',
                'cgi_url_value'
            ],
        ];
    }

    public function testGetOrderUrl()
    {
        $orderUrlPattern = 'order_url';
        $builtOrderUrl = 'built_url';
        $this->initializeMethodWithConfigMock([['place_order_url', null, $orderUrlPattern]]);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($orderUrlPattern)
            ->willReturn($builtOrderUrl);

        $this->formMock->setMethod($this->methodMock);

        $this->assertEquals($builtOrderUrl, $this->formMock->getOrderUrl());
    }

    public function testGetDateDelim()
    {
        $dateDelimiter = '/';
        $this->initializeMethodWithConfigMock([['date_delim', null, $dateDelimiter]]);

        $this->formMock->setMethod($this->methodMock);

        $this->assertEquals($dateDelimiter, $this->formMock->getDateDelim());
    }

    public function testGetCardFieldsMap()
    {
        $ccfields = 'x_card_code,x_exp_date,x_card_num';
        $this->initializeMethodWithConfigMock([['ccfields', null, $ccfields]]);

        $this->formMock->setMethod($this->methodMock);

        $expected = json_encode(['cccvv' => 'x_card_code', 'ccexpdate' => 'x_exp_date', 'ccnum' => 'x_card_num']);

        $this->assertEquals($expected, $this->formMock->getCardFieldsMap());
    }

    public function testToHtmlShouldRender()
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($this->methodMock);

        $this->formMock->toHtml();
    }

    public function testToHtmlShouldNotRenderEmptyQuote()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn(null);

        $this->assertEmpty($this->formMock->toHtml());
    }

    public function testToHtmlShouldNotRenderEmptyPayment()
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getPayment')
            ->willReturn(null);

        $this->assertEmpty($this->formMock->toHtml());
    }

    public function testGetMethodSuccess()
    {
        $this->formMock->setMethod($this->methodMock);
        $this->assertSame($this->methodMock, $this->formMock->getMethod());
    }

    public function testGetMethodNotTransparentInterface()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage((string)__('We cannot retrieve the transparent payment method model object.'));

        $methodMock = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();

        $this->formMock->setMethod($methodMock);
        $this->formMock->getMethod();
    }
}
