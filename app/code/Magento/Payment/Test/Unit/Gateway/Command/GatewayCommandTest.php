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
     * @var \Magento\Payment\Gateway\Http\TransferFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferFactoryMock;

    /**
     * @var \Magento\Payment\Gateway\Http\ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var \Magento\Payment\Gateway\Response\HandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseHandlerMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    protected function setUp()
    {
        $this->requestBuilderMock = $this->getMockBuilder('Magento\Payment\Gateway\Request\BuilderInterface')
            ->getMockForAbstractClass();
        $this->transferFactoryMock = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferFactoryInterface')
            ->getMockForAbstractClass();
        $this->clientMock = $this->getMockBuilder('Magento\Payment\Gateway\Http\ClientInterface')
            ->getMockForAbstractClass();
        $this->responseHandlerMock = $this->getMockBuilder('Magento\Payment\Gateway\Response\HandlerInterface')
            ->getMockForAbstractClass();
        $this->validatorMock = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ValidatorInterface')
            ->getMockForAbstractClass();

        $this->model = new \Magento\Payment\Gateway\Command\GatewayCommand(
            $this->requestBuilderMock,
            $this->transferFactoryMock,
            $this->clientMock,
            $this->responseHandlerMock,
            $this->validatorMock
        );
    }

    public function testExecute()
    {
        $commandSubject = ['authorize'];
        $request = ['request_field1' => 'request_value1', 'request_field2' => 'request_value2'];
        $response = ['response_field1' => 'response_value1'];
        $validationResult = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ResultInterface')
            ->getMockForAbstractClass();

        $transferO = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferInterface')
            ->getMockForAbstractClass();

        $this->requestBuilderMock->expects(static::once())
            ->method('build')
            ->with($commandSubject)
            ->willReturn($request);

        $this->transferFactoryMock->expects(static::once())
            ->method('create')
            ->with($request)
            ->willReturn($transferO);

        $this->clientMock->expects(static::once())
            ->method('placeRequest')
            ->with($transferO)
            ->willReturn($response);
        $this->validatorMock->expects(static::once())
            ->method('validate')
            ->with(array_merge($commandSubject, ['response' =>$response]))
            ->willReturn($validationResult);
        $validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->responseHandlerMock->expects(static::once())
            ->method('handle')
            ->with($commandSubject, $response);

        $this->model->execute($commandSubject);
    }
}
