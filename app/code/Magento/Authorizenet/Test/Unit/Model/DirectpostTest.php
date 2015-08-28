<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Authorizenet\Model\Directpost;

/**
 * Class DirectpostTest
 */
class DirectpostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Directpost
     */
    protected $directpost;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Authorizenet\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactoryMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMock();
        $this->paymentMock = $this->getMockBuilder('Magento\Payment\Model\InfoInterface')
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder('Magento\Authorizenet\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseFactoryMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Response\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Response')
            ->setMethods(
                [
                    'setData', 'isValidHash', 'getXTransId',
                    'getXResponseCode', 'getXResponseReasonText',
                    'getXAmount'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->responseMock);

        $helper = new ObjectManagerHelper($this);
        $this->directpost = $helper->getObject(
            'Magento\Authorizenet\Model\Directpost',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'dataHelper' => $this->dataHelperMock,
                'responseFactory' => $this->responseFactoryMock
            ]
        );
    }

    public function testGetConfigInterface()
    {
        $this->assertInstanceOf(
            'Magento\Payment\Model\Method\ConfigInterface',
            $this->directpost->getConfigInterface()
        );
    }

    public function testGetConfigValue()
    {
        $field = 'some_field';
        $returnValue = 'expected';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/' . $field)
            ->willReturn($returnValue);
        $this->assertEquals($returnValue, $this->directpost->getValue($field));
    }

    public function testSetDataHelper()
    {
        $storeId = 'store-id';
        $expectedResult = 'relay-url';

        $helperDataMock = $this->getMockBuilder('Magento\Authorizenet\Helper\Backend\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $helperDataMock->expects($this->once())
            ->method('getRelayUrl')
            ->with($storeId)
            ->willReturn($expectedResult);

        $this->directpost->setDataHelper($helperDataMock);
        $this->assertEquals($expectedResult, $this->directpost->getRelayUrl($storeId));
    }

    public function testAuthorize()
    {
        $paymentAction = 'some_action';

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/payment_action', 'store', null)
            ->willReturn($paymentAction);
        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('payment_type', $paymentAction);

        $this->directpost->authorize($this->paymentMock, 10);
    }

    public function testGetCgiUrl()
    {
        $url = 'cgi/url';

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/cgi_url', 'store', null)
            ->willReturn($url);

        $this->assertEquals($url, $this->directpost->getCgiUrl());
    }

    public function testGetCgiUrlWithEmptyConfigValue()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/cgi_url', 'store', null)
            ->willReturn(null);

        $this->assertEquals(Directpost::CGI_URL, $this->directpost->getCgiUrl());
    }

    public function testGetRelayUrl()
    {
        $storeId = 100;
        $url = 'relay/url';
        $this->directpost->setData('store', $storeId);

        $this->dataHelperMock->expects($this->any())
            ->method('getRelayUrl')
            ->with($storeId)
            ->willReturn($url);

        $this->assertEquals($url, $this->directpost->getRelayUrl());
        $this->assertEquals($url, $this->directpost->getRelayUrl($storeId));
    }

    public function testGetResponse()
    {
        $this->assertSame($this->responseMock, $this->directpost->getResponse());
    }

    public function testSetResponseData()
    {
        $data = [
            'key' => 'value'
        ];

        $this->responseMock->expects($this->once())
            ->method('setData')
            ->with($data)
            ->willReturnSelf();

        $this->assertSame($this->directpost, $this->directpost->setResponseData($data));
    }

    public function testValidateResponseSuccess()
    {
        $this->prepareTestValidateResponse('some_md5', 'login', true);
        $this->assertEquals(true, $this->directpost->validateResponse());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateResponseFailure()
    {
        $this->prepareTestValidateResponse('some_md5', 'login', false);
        $this->directpost->validateResponse();
    }

    /**
     * @param string $transMd5
     * @param string $login
     * @param bool $isValidHash
     */
    protected function prepareTestValidateResponse($transMd5, $login, $isValidHash)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['payment/authorizenet_directpost/trans_md5', 'store', null, $transMd5],
                    ['payment/authorizenet_directpost/login', 'store', null, $login]
                ]
            );
        $this->responseMock->expects($this->any())
            ->method('isValidHash')
            ->with($transMd5, $login)
            ->willReturn($isValidHash);
    }

    public function testCheckTransIdSuccess()
    {
        $this->responseMock->expects($this->once())
            ->method('getXTransId')
            ->willReturn('111');

        $this->assertEquals(true, $this->directpost->checkTransId());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCheckTransIdFailure()
    {
        $this->responseMock->expects($this->once())
            ->method('getXTransId')
            ->willReturn(null);

        $this->directpost->checkTransId();
    }

    /**
     * @param bool $responseCode
     *
     * @dataProvider checkResponseCodeSuccessDataProvider
     */
    public function testCheckResponseCodeSuccess($responseCode)
    {
        $this->responseMock->expects($this->once())
            ->method('getXResponseCode')
            ->willReturn($responseCode);

        $this->assertEquals(true, $this->directpost->checkResponseCode());
    }

    /**
     * @return array
     */
    public function checkResponseCodeSuccessDataProvider()
    {
        return [
            ['responseCode' => Directpost::RESPONSE_CODE_APPROVED],
            ['responseCode' => Directpost::RESPONSE_CODE_HELD]
        ];
    }

    /**
     * @param bool $responseCode
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider checkResponseCodeFailureDataProvider
     */
    public function testCheckResponseCodeFailure($responseCode)
    {
        $reasonText = 'reason text';

        $this->responseMock->expects($this->once())
            ->method('getXResponseCode')
            ->willReturn($responseCode);
        $this->responseMock->expects($this->any())
            ->method('getXResponseReasonText')
            ->willReturn($reasonText);
        $this->dataHelperMock->expects($this->any())
            ->method('wrapGatewayError')
            ->with($reasonText)
            ->willReturn(__('Gateway error: ' . $reasonText));

        $this->directpost->checkResponseCode();
    }

    /**
     * @return array
     */
    public function checkResponseCodeFailureDataProvider()
    {
        return [
            ['responseCode' => Directpost::RESPONSE_CODE_DECLINED],
            ['responseCode' => Directpost::RESPONSE_CODE_ERROR],
            ['responseCode' => 999999]
        ];
    }

    /**
     * @param bool $isInitializeNeeded
     *
     * @dataProvider setIsInitializeNeededDataProvider
     */
    public function testSetIsInitializeNeeded($isInitializeNeeded)
    {
        $this->directpost->setIsInitializeNeeded($isInitializeNeeded);
        $this->assertEquals($isInitializeNeeded, $this->directpost->isInitializeNeeded());
    }

    /**
     * @return array
     */
    public function setIsInitializeNeededDataProvider()
    {
        return [
            ['isInitializationNeeded' => true],
            ['isInitializationNeeded' => false]
        ];
    }

    /**
     * @param bool $isGatewayActionsLocked
     * @param bool $canCapture
     *
     * @dataProvider canCaptureDataProvider
     */
    public function testCanCapture($isGatewayActionsLocked, $canCapture)
    {
        $this->directpost->setData('info_instance', $this->paymentMock);

        $this->paymentMock->expects($this->any())
            ->method('getAdditionalInformation')
            ->with(Directpost::GATEWAY_ACTIONS_LOCKED_STATE_KEY)
            ->willReturn($isGatewayActionsLocked);

        $this->assertEquals($canCapture, $this->directpost->canCapture());
    }

    /**
     * @return array
     */
    public function canCaptureDataProvider()
    {
        return [
            ['isGatewayActionsLocked' => false, 'canCapture' => true],
            ['isGatewayActionsLocked' => true, 'canCapture' => false]
        ];
    }
}
