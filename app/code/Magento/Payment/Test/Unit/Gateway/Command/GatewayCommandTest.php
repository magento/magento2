<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
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
    private $requestBuilder;

    /**
     * @var TransferFactoryInterface|MockObject
     */
    private $transferFactory;

    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var HandlerInterface|MockObject
     */
    private $responseHandler;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validator;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ErrorMessageMapperInterface|MockObject
     */
    private $errorMessageMapper;

    protected function setUp(): void
    {
        $this->requestBuilder = $this->getMockForAbstractClass(BuilderInterface::class);
        $this->transferFactory = $this->getMockForAbstractClass(TransferFactoryInterface::class);
        $this->client = $this->getMockForAbstractClass(ClientInterface::class);
        $this->responseHandler = $this->getMockForAbstractClass(HandlerInterface::class);
        $this->validator = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->errorMessageMapper = $this->getMockForAbstractClass(ErrorMessageMapperInterface::class);

        $this->command = new GatewayCommand(
            $this->requestBuilder,
            $this->transferFactory,
            $this->client,
            $this->logger,
            $this->responseHandler,
            $this->validator,
            $this->errorMessageMapper
        );
    }

    public function testExecute()
    {
        $commandSubject = ['authorize'];
        $this->processRequest($commandSubject, true);

        $this->responseHandler->method('handle')
            ->with($commandSubject, ['response_field1' => 'response_value1']);

        $this->command->execute($commandSubject);
    }

    /**
     * Checks a case when request fails.
     */
    public function testExecuteValidationFail()
    {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Transaction has been declined. Please try again later.');
        $commandSubject = ['authorize'];
        $validationFailures = [
            __('Failure #1'),
            __('Failure #2'),
        ];

        $this->processRequest($commandSubject, false, $validationFailures);

        $this->logger->expects(self::exactly(count($validationFailures)))
            ->method('critical')
            ->willReturnCallback(
                function ($arg) use ($validationFailures) {
                    static $index = 0;
                    $expectedMessage = 'Payment Error: ' . $validationFailures[$index++];
                    if ($arg == $expectedMessage) {
                        return null;
                    }
                }
            );

        $this->command->execute($commandSubject);
    }

    /**
     * Checks a case when request fails and response errors are mapped.
     */
    public function testExecuteValidationFailWithMappedErrors()
    {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Failure Mapped');
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

        $this->logger->expects(self::exactly(count(array_merge($validationFailures, $errorCodes))))
            ->method('critical')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == 'Payment Error: Unauthorized' || $arg1 == 'Payment Error: Failure Mapped' ||
                    $arg1 == 'Payment Error: Failure #2') {
                    return null;
                }
            });

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

        $this->requestBuilder->method('build')
            ->with($commandSubject)
            ->willReturn($request);

        $this->transferFactory->method('create')
            ->with($request)
            ->willReturn($transferO);

        $this->client->method('placeRequest')
            ->with($transferO)
            ->willReturn($response);

        $result = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();

        $this->validator->method('validate')
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
