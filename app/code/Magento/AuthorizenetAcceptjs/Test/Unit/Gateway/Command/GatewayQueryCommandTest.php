<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Command\GatewayQueryCommand;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GatewayQueryCommandTest extends TestCase
{
    /**
     * @var GatewayQueryCommand
     */
    private $command;

    /**
     * @var BuilderInterface|MockObject|InvocationMocker
     */
    private $requestBuilderMock;

    /**
     * @var TransferFactoryInterface|MockObject|InvocationMocker
     */
    private $transferFactoryMock;

    /**
     * @var ClientInterface|MockObject|InvocationMocker
     */
    private $clientMock;

    /**
     * @var LoggerInterface|MockObject|InvocationMocker
     */
    private $loggerMock;

    /**
     * @var ValidatorInterface|MockObject|InvocationMocker
     */
    private $validatorMock;

    /**
     * @var TransferInterface|MockObject|InvocationMocker
     */
    private $transferMock;

    protected function setUp()
    {
        $this->requestBuilderMock = $this->createMock(BuilderInterface::class);
        $this->transferFactoryMock = $this->createMock(TransferFactoryInterface::class);
        $this->transferMock = $this->createMock(TransferInterface::class);
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->command = new GatewayQueryCommand(
            $this->requestBuilderMock,
            $this->transferFactoryMock,
            $this->clientMock,
            $this->loggerMock,
            $this->validatorMock
        );
    }

    public function testNormalExecution()
    {
        $buildSubject = [
            'foo' => '123'
        ];

        $request = [
            'bar' => '321'
        ];

        $response = [
            'transaction' => [
                'transactionType' => 'foo',
                'transactionStatus' => 'bar',
                'responseCode' => 'baz'
            ]
        ];

        $validationSubject = $buildSubject;
        $validationSubject['response'] = $response;

        $this->requestBuilderMock->method('build')
            ->with($buildSubject)
            ->willReturn($request);

        $this->transferFactoryMock->method('create')
            ->with($request)
            ->willReturn($this->transferMock);

        $this->clientMock->method('placeRequest')
            ->with($this->transferMock)
            ->willReturn($response);

        $this->validatorMock->method('validate')
            ->with($validationSubject)
            ->willReturn(new Result(true));

        $result = $this->command->execute($buildSubject);

        $this->assertInstanceOf(ArrayResult::class, $result);
        $this->assertEquals($response, $result->get());
    }

    /**
     * @expectedExceptionMessage There was an error while trying to process the request.
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     */
    public function testExceptionIsThrownAndLoggedWhenRequestFails()
    {
        $buildSubject = [
            'foo' => '123'
        ];

        $request = [
            'bar' => '321'
        ];

        $this->requestBuilderMock->method('build')
            ->with($buildSubject)
            ->willReturn($request);

        $this->transferFactoryMock->method('create')
            ->with($request)
            ->willReturn($this->transferMock);

        $e = new \Exception('foobar');

        $this->clientMock->method('placeRequest')
            ->with($this->transferMock)
            ->willThrowException($e);

        // assert the exception is logged
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($e);

        $this->command->execute($buildSubject);
    }
    /**
     * @expectedExceptionMessage There was an error while trying to process the request.
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     */
    public function testExceptionIsThrownWhenResponseIsInvalid()
    {
        $buildSubject = [
            'foo' => '123'
        ];

        $request = [
            'bar' => '321'
        ];

        $response = [
            'baz' => '456'
        ];

        $validationSubject = $buildSubject;
        $validationSubject['response'] = $response;

        $this->requestBuilderMock->method('build')
            ->with($buildSubject)
            ->willReturn($request);

        $this->transferFactoryMock->method('create')
            ->with($request)
            ->willReturn($this->transferMock);

        $this->clientMock->method('placeRequest')
            ->with($this->transferMock)
            ->willReturn($response);

        $this->validatorMock->method('validate')
            ->with($validationSubject)
            ->willReturn(new Result(false));

        $this->command->execute($buildSubject);
    }
}
