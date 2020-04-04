<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GatewayCommandTest extends TestCase
{
    /**
     * @var GatewayCommand
     */
    private $command;

    /**
     * @var BuilderInterface|MockObject
     */
    private $requestBuilderMock;

    /**
     * @var TransferFactoryInterface|MockObject
     */
    private $transferFactoryMock;

    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var HandlerInterface|MockObject
     */
    private $responseHandlerMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ErrorMessageMapperInterface|MockObject
     */
    private $errorMessageMapper;

    protected function setUp()
    {
        $this->requestBuilderMock = $this->createMock(BuilderInterface::class);
        $this->transferFactoryMock = $this->createMock(TransferFactoryInterface::class);
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->responseHandlerMock = $this->createMock(HandlerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->errorMessageMapper = $this->createMock(ErrorMessageMapperInterface::class);

        $this->command = new GatewayCommand(
            $this->requestBuilderMock,
            $this->transferFactoryMock,
            $this->clientMock,
            $this->loggerMock,
            $this->responseHandlerMock,
            $this->validatorMock,
            $this->errorMessageMapper
        );
    }

    public function testExecute()
    {
        $commandSubject = ['authorize'];
        $this->processRequest($commandSubject, true);

        $this->responseHandlerMock->method('handle')
            ->with($commandSubject, ['response_field1' => 'response_value1']);

        $this->command->execute($commandSubject);
    }

    /**
     * Checks a case when request fails.
     *
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     * @expectedExceptionMessage Transaction has been declined. Please try again later.
     */
    public function testExecuteValidationFail()
    {
        $commandSubject = ['authorize'];
        $validationFailures = [
            __('Failure #1'),
            __('Failure #2'),
        ];

        $this->processRequest($commandSubject, false, $validationFailures);

        $this->loggerMock->expects(self::exactly(count($validationFailures)))
            ->method('critical')
            ->withConsecutive(
                [self::equalTo('Payment Error: ' . $validationFailures[0])],
                [self::equalTo('Payment Error: ' . $validationFailures[1])]
            );

        $this->command->execute($commandSubject);
    }

    /**
     * Checks a case when request fails and response errors are mapped.
     *
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     * @expectedExceptionMessage Failure Mapped
     */
    public function testExecuteValidationFailWithMappedErrors()
    {
        $commandSubject = ['authorize'];
        $validationFailures = [
            __('Failure #1'),
            __('Failure #2'),
        ];
        $errorCodes = ['401'];

        $this->processRequest($commandSubject, false, $validationFailures, $errorCodes);

        $this->errorMessageMapper->method('getMessage')
            ->willReturnMap(
                [
                    ['401', 'Unauthorized'],
                    ['Failure #1', 'Failure Mapped'],
                    ['Failure #2', null]
                ]
            );

        $this->loggerMock->expects(self::exactly(count(array_merge($validationFailures, $errorCodes))))
            ->method('critical')
            ->withConsecutive(
                [self::equalTo('Payment Error: Unauthorized')],
                [self::equalTo('Payment Error: Failure Mapped')],
                [self::equalTo('Payment Error: Failure #2')]
            );

        $this->command->execute($commandSubject);
    }

    /**
     * Performs command actions like request, response and validation.
     *
     * @param array $commandSubject
     * @param bool $validationResult
     * @param array $validationFailures
     * @param array $errorCodes
     */
    private function processRequest(
        array $commandSubject,
        bool $validationResult,
        array $validationFailures = [],
        array $errorCodes = []
    ) {
        $request = [
            'request_field1' => 'request_value1',
            'request_field2' => 'request_value2'
        ];
        $response = ['response_field1' => 'response_value1'];
        $transferO = $this->getMockBuilder(TransferInterface::class)
            ->getMockForAbstractClass();

        $this->requestBuilderMock->method('build')
            ->with($commandSubject)
            ->willReturn($request);

        $this->transferFactoryMock->method('create')
            ->with($request)
            ->willReturn($transferO);

        $this->clientMock->method('placeRequest')
            ->with($transferO)
            ->willReturn($response);

        $result = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();

        $this->validatorMock->method('validate')
            ->with(array_merge($commandSubject, ['response' => $response]))
            ->willReturn($result);
        $result->method('isValid')
            ->willReturn($validationResult);
        $result->method('getFailsDescription')
            ->willReturn($validationFailures);
        $result->method('getErrorCodes')
            ->willReturn($errorCodes);
    }
}
