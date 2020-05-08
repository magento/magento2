<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\System\Config\Validatevat;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Controller\Adminhtml\System\Config\Validatevat\Validate;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateTest extends TestCase
{
    /**
     * @var Validate
     */
    protected $controller;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Json|MockObject
     */
    protected $resultJson;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var Http|MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $resultJsonFactory = $this->mockResultJson();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->request,
                'objectManager' => $this->objectManager,
            ]
        );
        $this->controller = $objectManager->getObject(
            Validate::class,
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

        $gatewayResponse = new DataObject([
            'is_valid' => $isValid,
            'request_message' => $requestMessage,
        ]);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['country', null, $country],
                ['vat', null, $vat],
            ]);

        $vatMock = $this->getMockBuilder(Vat::class)
            ->disableOriginalConstructor()
            ->getMock();

        $vatMock->expects($this->once())
            ->method('checkVatNumber')
            ->with($country, $vat)
            ->willReturn($gatewayResponse);

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(Vat::class)
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
     * @return MockObject
     */
    protected function mockResultJson()
    {
        $this->resultJson = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultJson);

        return $resultJsonFactory;
    }
}
