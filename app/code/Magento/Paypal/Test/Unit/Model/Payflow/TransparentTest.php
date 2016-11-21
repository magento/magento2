<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow;

use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class TransparentTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Transparent
 */
class TransparentTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Model\Payflow\Transparent|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var \Magento\Paypal\Model\Payflow\Service\Gateway|\PHPUnit_Framework_MockObject_MockObject */
    protected $gatewayMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Payment\Model\Method\ConfigInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $configFactoryMock;

    /** @var \Magento\Payment\Model\Method\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
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
     * @var \PHPUnit_Framework_MockObject_MockObject|
     * \Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator
     */
    protected $responseValidator;

    protected function setUp()
    {
        $this->paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Info')
            ->setMethods(
                [
                    'getOrder',
                    'getAdditionalInformation',
                    'setTransactionId',
                    'setIsTransactionClosed',
                    'setShouldCloseParentTransaction',
                    'getParentTransactionId',
                    'setParentTransactionId',
                ]
            )
            ->disableOriginalConstructor()
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
        $this->responseMock = $this->getMockBuilder('\Magento\Framework\DataObject')
            ->setMethods(['getResultCode', 'getOrigresult', 'getRespmsg', 'getPnref'])
            ->disableOriginalConstructor()
            ->getMock();
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
                'responseValidator' => $this->responseValidator
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
                'getId', 'getIncrementId', 'getBaseCurrencyCode'
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
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function crateVoidResponseMock()
    {
        $voidResponseMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(
                [
                    'getResultCode',
                    'getPnref'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $voidResponseMock->expects($this->any())
            ->method('getResultCode')
            ->willReturn(Transparent::RESPONSE_CODE_APPROVED);
        $voidResponseMock->expects($this->any())
            ->method('getPnref')
            ->willReturn('test-pnref');

        $this->responseMock->expects($this->once())
            ->method('getPnref')
            ->willReturn('test-pnref');
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
     * @expectedExceptionMessage Error processing payment, please try again later.
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
            ->with('pnref')
            ->willReturn('test-pnref');

        $this->responseMock->expects($this->any())
            ->method('getResultCode')
            ->willReturn(Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER);

        $this->object->authorize($this->paymentMock, 33);
    }

    /**
     * @param int $resultCodeExactlyCall
     * @param int $resultCode
     * @param int $origResultExactlyCall
     * @param int $origResult
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider authorizeLocalizedExceptionDataProvider
     */
    public function testAuthorizeLocalizedException(
        $resultCodeExactlyCall,
        $resultCode,
        $origResultExactlyCall,
        $origResult
    ) {
        $this->initializationAuthorizeMock();
        $this->buildRequestData();

        $this->responseMock->expects($this->exactly($resultCodeExactlyCall))
            ->method('getResultCode')
            ->willReturn($resultCode);
        $this->responseMock->expects($this->exactly($origResultExactlyCall))
            ->method('getOrigresult')
            ->willReturn($origResult);

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
                'resultCodeExactlyCall' => 2,
                'origResult' => Payflowpro::RESPONSE_CODE_APPROVED,
                'origResultExactlyCall' => 1,
                'resultCode' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
            ],
            [
                'resultCodeExactlyCall' => 3,
                'origResult' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER,
                'origResultExactlyCall' => 1,
                'resultCode' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
            ],
            [
                'resultCodeExactlyCall' => 3,
                'origResult' => Payflowpro::RESPONSE_CODE_DECLINED,
                'origResultExactlyCall' => 0,
                'resultCode' => 1111111111
            ],
            [
                'resultCodeExactlyCall' => 3,
                'origResult' => 3432432423,
                'origResultExactlyCall' => 0,
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

        $this->responseMock->expects($this->any())
            ->method('getResultCode')
            ->willReturn(Payflowpro::RESPONSE_CODE_APPROVED);
        $this->responseMock->expects($this->any())->method('getOrigresult')->willReturn(0);

        $this->gatewayMock->expects($this->once())->method('postRequest')->willReturn($this->responseMock);

        $this->responseValidator->expects($this->once())
            ->method('validate')
            ->with($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getPnref')
            ->willReturn('test-pnref');

        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('test-pnref')
            ->willReturnSelf();
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(0);

        $this->assertSame($this->object, $this->object->authorize($this->paymentMock, 33));
    }
}
