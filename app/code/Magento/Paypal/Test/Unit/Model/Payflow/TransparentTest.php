<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow;

use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;

/**
 * Class TransparentTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Transparent
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransparentTest extends \PHPUnit_Framework_TestCase
{
    /** @var Transparent|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var \Magento\Paypal\Model\Payflow\Service\Gateway|\PHPUnit_Framework_MockObject_MockObject */
    protected $gatewayMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Payment\Model\Method\ConfigInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $configFactoryMock;

    /** @var \Magento\Payment\Model\Method\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\DataObject */
    protected $responseMock;

    /** @var \Magento\Sales\Model\Order\Payment\Info|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentMock;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderMock;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressBillingMock;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressShippingMock;

    /**
     * @var CreditCardTokenFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|
     * \Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator
     */
    protected $responseValidator;

    protected function setUp()
    {
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenFactory = $this->getMockBuilder(CreditCardTokenFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->gatewayMock = $this->getMockBuilder('\Magento\Paypal\Model\Payflow\Service\Gateway')
            ->setMethods(['postRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->setMethods(['getStore', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturnSelf();
        $this->configMock = $this->getMockBuilder('Magento\Paypal\Model\PayflowConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFactoryMock = $this->getMockBuilder('\Magento\Payment\Model\Method\ConfigInterfaceFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->configMock);
        $this->responseMock = new \Magento\Framework\DataObject();
        $this->responseValidator = $this->getMockBuilder(
            'Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator'
        )->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectHelper->getObject(
            'Magento\Paypal\Model\Payflow\Transparent',
            [
                'gateway' => $this->gatewayMock,
                'storeManager' => $this->storeManagerMock,
                'configFactory' => $this->configFactoryMock,
                'responseValidator' => $this->responseValidator,
                'paymentTokenFactory' => $this->paymentTokenFactory
            ]
        );
    }

    /**
     * Initializing a collection Mock for Authorize method
     *
     * @return void
     */
    protected function initializationAuthorizeMock()
    {
        $this->orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods([
                'getCustomerId', 'getBillingAddress', 'getShippingAddress', 'getCustomerEmail',
                'getId', 'getIncrementId'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressBillingMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreet',
                    'getCity',
                    'getRegionCode',
                    'getPostcode',
                    'getCountryId'
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $this->addressShippingMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreet',
                    'getCity',
                    'getRegionCode',
                    'getPostcode',
                    'getCountryId'
                ]
            )->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Build data for request for operation Authorize
     *
     * @return void
     */
    protected function buildRequestData()
    {
        $this->paymentMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->addressBillingMock);
        $this->orderMock->expects(static::once())
            ->method('getId')
            ->willReturn(1);
        $this->orderMock->expects(static::once())
            ->method('getIncrementId')
            ->willReturn('0000001');
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->addressShippingMock);
        $this->addressBillingMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn('Firstname');
        $this->addressBillingMock->expects($this->once())
            ->method('getLastname')
            ->willReturn('Lastname');
        $this->addressBillingMock->expects($this->once())
            ->method('getStreet')
            ->willReturn(['street-1', 'street-2']);
        $this->addressBillingMock->expects($this->once())
            ->method('getCity')
            ->willReturn('City');
        $this->addressBillingMock->expects($this->once())
            ->method('getRegionCode')
            ->willReturn('RegionCode');
        $this->addressBillingMock->expects($this->once())
            ->method('getPostcode')
            ->willReturn('Postcode');
        $this->addressBillingMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('CountryId');
        $this->orderMock->expects($this->once())
            ->method('getCustomerEmail')
            ->willReturn('customer@email.com');
        $this->addressShippingMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn('Firstname');
        $this->addressShippingMock->expects($this->once())
            ->method('getLastname')
            ->willReturn('Lastname');
        $this->addressShippingMock->expects($this->once())
            ->method('getStreet')
            ->willReturn(['street-1', 'street-2']);
        $this->addressShippingMock->expects($this->once())
            ->method('getCity')
            ->willReturn('City');
        $this->addressShippingMock->expects($this->once())
            ->method('getRegionCode')
            ->willReturn('RegionCode');
        $this->addressShippingMock->expects($this->once())
            ->method('getPostcode')
            ->willReturn('Postcode');
        $this->addressShippingMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('CountryId');
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function crateVoidResponseMock()
    {
        $voidResponseMock = new \Magento\Framework\DataObject(
            [
                'result_code' => Transparent::RESPONSE_CODE_APPROVED,
                'pnref' => 'test-pnref'
            ]
        );

        $this->responseMock->setData(Transparent::PNREF, 'test-pnref');

        $this->paymentMock->expects($this->once())
            ->method('setParentTransactionId')
            ->with('test-pnref');
        $this->paymentMock->expects($this->once())
            ->method('getParentTransactionId')
            ->willReturn('test-pnref');
        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('test-pnref')
            ->willReturnSelf();
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(1)
            ->willReturnSelf();
        $this->paymentMock->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(1);

        return $voidResponseMock;
    }

    /**
     * @expectedException  \Exception
     */
    public function testAuthorizeException()
    {
        $this->initializationAuthorizeMock();
        $this->buildRequestData();

        $this->gatewayMock->expects($this->once())
            ->method('postRequest')
            ->willThrowException(new \Exception());

        $this->object->authorize($this->paymentMock, 33);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error processing payment. Please try again later.
     */
    public function testAuthorizeValidationException()
    {
        $this->initializationAuthorizeMock();
        $this->buildRequestData();
        $voidResponseMock = $this->crateVoidResponseMock();

        $this->gatewayMock->expects($this->at(0))
            ->method('postRequest')
            ->willReturn($this->responseMock);

        $this->responseValidator->expects($this->once())
            ->method('validate')
            ->with($this->responseMock)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Error')));

        $this->gatewayMock->expects($this->at(1))
            ->method('postRequest')
            ->willReturn($voidResponseMock);

        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(Payflowpro::PNREF)
            ->willReturn('test-pnref');

        $this->responseMock->setData('result_code', Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER);

        $this->object->authorize($this->paymentMock, 33);
    }

    /**
     * @param int $resultCode
     * @param int $origResult
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider authorizeLocalizedExceptionDataProvider
     */
    public function testAuthorizeLocalizedException(
        $resultCode,
        $origResult
    ) {
        $this->initializationAuthorizeMock();
        $this->buildRequestData();

        $this->responseMock->setData('result_code', $resultCode);
        $this->responseMock->setData('origresult', $origResult);

        $this->gatewayMock->expects($this->exactly(1))
            ->method('postRequest')
            ->willReturn($this->responseMock);
        $this->object->authorize($this->paymentMock, 33);
    }

    /**
     * @return array
     */
    public function authorizeLocalizedExceptionDataProvider()
    {
        return [
            [
                'origResult' => Payflowpro::RESPONSE_CODE_APPROVED,
                'resultCode' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
            ],
            [
                'origResult' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER,
                'resultCode' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
            ],
            [
                'origResult' => Payflowpro::RESPONSE_CODE_DECLINED,
                'resultCode' => 1111111111
            ],
            [
                'origResult' => 3432432423,
                'resultCode' => 23233432423
            ],
        ];
    }

    /**
     * Test method
     * with resultCode = RESPONSE_CODE_APPROVED and Origresult != RESPONSE_CODE_FRAUDSERVICE_FILTER
     */
    public function testAuthorize()
    {
        $this->initializationAuthorizeMock();
        $this->buildRequestData();

        $paymentTokenMock = $this->getMock(PaymentTokenInterface::class);
        $extensionAttributes = $this->getMockBuilder('Magento\Sales\Api\Data\OrderPaymentExtensionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setVaultPaymentToken'])
            ->getMock();
        $ccDetails = [
            'cc_type' => 'VI',
            'cc_number' => '1111'
        ];

        $this->responseMock->setData('result_code', Payflowpro::RESPONSE_CODE_APPROVED);
        $this->responseMock->setData('origresult', 0);
        $this->responseMock->setData('pnref', 'test-pnref');

        $this->gatewayMock->expects($this->once())->method('postRequest')->willReturn($this->responseMock);

        $this->responseValidator->expects($this->once())
            ->method('validate')
            ->with($this->responseMock);

        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('test-pnref')
            ->willReturnSelf();
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(0);
        $this->paymentMock->expects($this->once())
            ->method('getCcExpYear')
            ->willReturn('2017');
        $this->paymentMock->expects($this->once())
            ->method('getCcExpMonth')
            ->willReturn('12');
        $this->paymentMock->expects(static::any())
            ->method('getAdditionalInformation')
            ->willReturnMap(
                [
                    [Transparent::CC_DETAILS, $ccDetails],
                    [Transparent::PNREF, 'test-pnref']
                ]
            );

        $this->paymentTokenFactory->expects(static::once())
            ->method('create')
            ->willReturn($paymentTokenMock);
        $paymentTokenMock->expects(static::once())
            ->method('setGatewayToken')
            ->with('test-pnref');
        $paymentTokenMock->expects(static::once())
            ->method('setTokenDetails')
            ->with(json_encode($ccDetails));
        $paymentTokenMock->expects(static::once())
            ->method('setExpiresAt')
            ->with('2018-01-01 00:00:00');

        $this->paymentMock->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects(static::once())
            ->method('setVaultPaymentToken')
            ->with($paymentTokenMock);

        $this->paymentMock->expects($this->at(8))
            ->method('unsAdditionalInformation')
            ->with(Transparent::CC_DETAILS);
        $this->paymentMock->expects($this->at(9))
            ->method('unsAdditionalInformation')
            ->with(Transparent::PNREF);

        $this->assertSame($this->object, $this->object->authorize($this->paymentMock, 33));
    }
}
