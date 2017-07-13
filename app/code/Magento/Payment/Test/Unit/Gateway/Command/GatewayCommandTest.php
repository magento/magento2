<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GatewayCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var GatewayCommand */
    protected $command;

    /**
     * @var BuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuilderMock;

    /**
     * @var TransferFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferFactoryMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var HandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseHandlerMock;

    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var LoggerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->requestBuilderMock = $this->getMock(
            BuilderInterface::class
        );
        $this->transferFactoryMock = $this->getMock(
            TransferFactoryInterface::class
        );
        $this->clientMock = $this->getMock(
            ClientInterface::class
        );
        $this->responseHandlerMock = $this->getMock(
            HandlerInterface::class
        );
        $this->validatorMock = $this->getMock(
            ValidatorInterface::class
        );
        $this->logger = $this->getMock(LoggerInterface::class);

        $this->command = new GatewayCommand(
            $this->requestBuilderMock,
            $this->transferFactoryMock,
            $this->clientMock,
            $this->logger,
            $this->responseHandlerMock,
            $this->validatorMock
        );
    }

    public function testExecute()
    {
        $commandSubject = ['authorize'];
        $request = [
            'request_field1' => 'request_value1',
            'request_field2' => 'request_value2'
        ];
        $response = ['response_field1' => 'response_value1'];
        $validationResult = $this->getMockBuilder(
            \Magento\Payment\Gateway\Validator\ResultInterface::class
        )
            ->getMockForAbstractClass();

        $transferO = $this->getMockBuilder(
            \Magento\Payment\Gateway\Http\TransferInterface::class
        )
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

        $this->command->execute($commandSubject);
    }

    public function testExecuteValidationFail()
    {
        $this->setExpectedException(
            \Magento\Payment\Gateway\Command\CommandException::class
        );

        $commandSubject = ['authorize'];
        $request = [
            'request_field1' => 'request_value1',
            'request_field2' => 'request_value2'
        ];
        $response = ['response_field1' => 'response_value1'];
        $validationFailures = [
            __('Failure #1'),
            __('Failure #2'),
        ];
        $validationResult = $this->getMockBuilder(
            \Magento\Payment\Gateway\Validator\ResultInterface::class
        )
            ->getMockForAbstractClass();

        $transferO = $this->getMockBuilder(
            \Magento\Payment\Gateway\Http\TransferInterface::class
        )
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
            ->willReturn(false);
        $validationResult->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn(
                $validationFailures
            );

        $this->logger->expects(static::exactly(count($validationFailures)))
            ->method('critical')
            ->withConsecutive(
                [$validationFailures[0]],
                [$validationFailures[1]]
            );

        $this->command->execute($commandSubject);
    }
}
