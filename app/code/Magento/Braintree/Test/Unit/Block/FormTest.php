<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $onePageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionQuoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentConfigMock;

    /**
     * @var \Magento\Braintree\Block\Form
     */
    protected $form;

    public function setUp()
    {
        $this->onePageMock = $this->getMockBuilder('\Magento\Checkout\Model\Type\Onepage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->vaultMock = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataHelperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getAppState'])
            ->getMock();

        $this->sessionQuoteMock = $this->getMockBuilder('\Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote'])
            ->getMock();

        $this->appStateMock = $this->getMockBuilder('\Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getAppState')
            ->willReturn($this->appStateMock);

        $this->paymentConfigMock = $this->getMockBuilder('\Magento\Payment\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->form = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Block\Form',
            [
                'onepage' => $this->onePageMock,
                'vault' => $this->vaultMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'config' => $this->configMock,
                'dataHelper' => $this->dataHelperMock,
                'context' => $this->contextMock,
                'sessionQuote' => $this->sessionQuoteMock,
                'paymentConfig' => $this->paymentConfigMock
            ]
        );
    }

    public function setMethodInfo()
    {
        $paymentMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn('method');

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMock();

        $quoteMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $this->onePageMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->form->setMethodInfo();
    }

    public function testGetStoredCards()
    {
        $card = new \stdClass();
        $card->cardType = 'VI';

        $this->vaultMock->expects($this->once())
            ->method('currentCustomerStoredCards')
            ->willReturn([$card]);

        $addressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId'])
            ->getMock();

        $addressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('US');

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress'])
            ->getMock();

        $quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($addressMock);

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->configMock->expects($this->once())
            ->method('getApplicableCardTypes')
            ->with('US')
            ->willReturn(['VI', 'DI', 'MC', 'AE', 'JCB']);

        $this->dataHelperMock->expects($this->once())
            ->method('getCcTypeCodeByName')
            ->with('VI')
            ->willReturn('VI');

        $result = $this->form->getStoredCards();
        $this->assertSame($result, [$card]);
    }

    public function testGetCcAvailableTypesWithSession()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $addressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId'])
            ->getMock();

        $addressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('US');

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress'])
            ->getMock();

        $quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($addressMock);

        $this->sessionQuoteMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->configMock->expects($this->once())
            ->method('getApplicableCardTypes')
            ->with('US')
            ->willReturn(['VI', 'DI', 'MC', 'AE', 'JCB']);

        $this->paymentConfigMock->expects($this->once())
            ->method('getCcTypes')
            ->willReturn(['VI' => 'VI', 'DI' => 'DI', 'MC' => 'MC', 'AE' => 'AE', 'JCB' => 'JCB']);

        $result = $this->form->getCcAvailableTypes();
        $this->assertSame($result, ['VI' => 'VI', 'DI' => 'DI', 'MC' => 'MC', 'AE' => 'AE', 'JCB' => 'JCB']);
    }

    public function testGetCcAvailableTypesWithCheckout()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('');

        $addressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId'])
            ->getMock();

        $addressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('US');

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress'])
            ->getMock();

        $quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($addressMock);

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->configMock->expects($this->once())
            ->method('getApplicableCardTypes')
            ->with('US')
            ->willReturn(['VI', 'DI', 'MC', 'AE', 'JCB']);

        $this->paymentConfigMock->expects($this->once())
            ->method('getCcTypes')
            ->willReturn(['VI' => 'VI', 'DI' => 'DI', 'MC' => 'MC', 'AE' => 'AE', 'JCB' => 'JCB']);

        $result = $this->form->getCcAvailableTypes();
        $this->assertSame($result, ['VI' => 'VI', 'DI' => 'DI', 'MC' => 'MC', 'AE' => 'AE', 'JCB' => 'JCB']);
    }
}
