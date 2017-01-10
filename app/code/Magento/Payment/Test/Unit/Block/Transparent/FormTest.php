<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block\Transparent;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\Method\TransparentInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormTesting | \PHPUnit_Framework_MockObject_MockObject
     */
    private $form;

    /**
     * @var RequestInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TransparentInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $methodMock;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        $context = $objectManagerHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            [
                'request' => $this->requestMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );

        $this->methodMock = $this->getMockBuilder(\Magento\Payment\Model\Method\TransparentInterface::class)
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentConfigMock = $this->getMockBuilder(\Magento\Payment\Model\Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = new FormTesting(
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

        $this->assertTrue($this->form->isAjaxRequest());
        $this->assertFalse($this->form->isAjaxRequest());
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

        $this->form->setMethod($this->methodMock);

        $this->assertEquals($expected, $this->form->getMethodConfigData($fieldName));
    }

    /**
     * Initializes method mock with config mock
     *
     * @param array $configMap
     */
    private function initializeMethodWithConfigMock(array $configMap = [])
    {
        $configInterface = $this->getMockBuilder(\Magento\Payment\Model\Method\ConfigInterface::class)
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
     * @see testGetMethodConfigData
     *
     * @case #1 Set string value
     * @case #2 Set boolean value
     *
     * @return array
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

        $this->form->setMethod($this->methodMock);

        $this->assertEquals($expectedUrl, $this->form->getCgiUrl());
    }

    /**
     * Data provider for testGetCgiUrl
     *
     * @see testGetCgiUrl
     *
     * @case #1 The sandboxFlag is 1 we expected cgi_url_test_mode_value
     * @case #2 The sandboxFlag is 0 we expected cgi_url_value
     *
     * @return array
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

        $this->form->setMethod($this->methodMock);

        $this->assertEquals($builtOrderUrl, $this->form->getOrderUrl());
    }

    public function testGetDateDelim()
    {
        $dateDelimiter = '/';
        $this->initializeMethodWithConfigMock([['date_delim', null, $dateDelimiter]]);

        $this->form->setMethod($this->methodMock);

        $this->assertEquals($dateDelimiter, $this->form->getDateDelim());
    }

    public function testGetCardFieldsMap()
    {
        $ccfields = 'x_card_code,x_exp_date,x_card_num';
        $this->initializeMethodWithConfigMock([['ccfields', null, $ccfields]]);

        $this->form->setMethod($this->methodMock);

        $expected = json_encode(['cccvv' => 'x_card_code', 'ccexpdate' => 'x_exp_date', 'ccnum' => 'x_card_num']);

        $this->assertEquals($expected, $this->form->getCardFieldsMap());
    }

    public function testToHtmlShouldRender()
    {
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
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

        $this->form->toHtml();
    }

    public function testToHtmlShouldNotRenderEmptyQuote()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn(null);

        $this->assertEmpty($this->form->toHtml());
    }

    public function testToHtmlShouldNotRenderEmptyPayment()
    {
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getPayment')
            ->willReturn(null);

        $this->assertEmpty($this->form->toHtml());
    }

    public function testGetMethodSuccess()
    {
        $this->form->setMethod($this->methodMock);
        $this->assertSame($this->methodMock, $this->form->getMethod());
    }

    public function testGetMethodNotTransparentInterface()
    {
        $this->setExpectedException(
            \Magento\Framework\Exception\LocalizedException::class,
            __('We cannot retrieve the transparent payment method model object.')
        );

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();

        $this->form->setMethod($methodMock);
        $this->form->getMethod();
    }
}
