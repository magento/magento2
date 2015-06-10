<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Command;

class GatewayCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Gateway\Command\GatewayCommand */
    protected $model;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuilderMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferBuilderMock;

    /**
     * @var \Magento\Payment\Gateway\Http\ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gatewayMock;

    /**
     * @var \Magento\Payment\Gateway\Response\HandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseHandlerMock;

    protected function setUp()
    {
        $this->requestBuilderMock = $this->getMockBuilder('Magento\Payment\Gateway\Request\BuilderInterface')
            ->getMockForAbstractClass();
        $this->transferBuilderMock = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferBuilderInterface')
            ->getMockForAbstractClass();
        $this->gatewayMock = $this->getMockBuilder('Magento\Payment\Gateway\Http\ClientInterface')
            ->getMockForAbstractClass();
        $this->responseHandlerMock = $this->getMockBuilder('Magento\Payment\Gateway\Response\HandlerInterface')
            ->getMockForAbstractClass();

        $this->model = new \Magento\Payment\Gateway\Command\GatewayCommand(
            $this->requestBuilderMock,
            $this->transferBuilderMock,
            $this->gatewayMock,
            $this->responseHandlerMock
        );
    }

    public function testExecute()
    {
        $commandSubject = ['authorize'];
        $request = ['request_field1' => 'request_value1', 'request_field2' => 'request_value2'];
        $response = ['response_field1' => 'response_value1'];

        $transferO = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferInterface')
            ->getMockForAbstractClass();

        $this->requestBuilderMock->expects($this->once())
            ->method('build')
            ->with($commandSubject)
            ->willReturn($request);

        $this->transferBuilderMock->expects($this->once())
            ->method('build')
            ->with($request)
            ->willReturn($transferO);

        $this->gatewayMock->expects($this->once())
            ->method('placeRequest')
            ->with($transferO)
            ->willReturn($response);

        $this->responseHandlerMock->expects($this->once())
            ->method('handle')
            ->with($commandSubject, $response);

        $this->model->execute($commandSubject);
    }
}
