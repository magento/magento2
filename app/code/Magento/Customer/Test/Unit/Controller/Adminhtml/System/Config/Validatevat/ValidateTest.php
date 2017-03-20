<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\System\Config\Validatevat;

class ValidateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\System\Config\Validatevat\Validate
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\Result\Json | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJson;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $resultJsonFactory = $this->mockResultJson();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->request,
                'objectManager' => $this->objectManager,
            ]
        );
        $this->controller = $objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\System\Config\Validatevat\Validate::class,
            [
                'context' => $this->context,
                'resultJsonFactory' => $resultJsonFactory,
            ]
        );
    }

    public function testExecute()
    {
        $country = 'US';
        $vat = '123456789';

        $isValid = true;
        $requestMessage = 'test';

        $json = '{"valid":' . (int)$isValid . ',"message":"' . $requestMessage . '"}';

        $gatewayResponse = new \Magento\Framework\DataObject([
            'is_valid' => $isValid,
            'request_message' => $requestMessage,
        ]);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['country', null, $country],
                ['vat', null, $vat],
            ]);

        $vatMock = $this->getMockBuilder(\Magento\Customer\Model\Vat::class)
            ->disableOriginalConstructor()
            ->getMock();

        $vatMock->expects($this->once())
            ->method('checkVatNumber')
            ->with($country, $vat)
            ->willReturn($gatewayResponse);

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Customer\Model\Vat::class)
            ->willReturn($vatMock);

        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'valid' => $gatewayResponse->getIsValid(),
                'message' => $gatewayResponse->getRequestMessage()
            ])
            ->willReturn($json);

        $this->assertEquals($json, $this->controller->execute());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockResultJson()
    {
        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultJson);

        return $resultJsonFactory;
    }
}
