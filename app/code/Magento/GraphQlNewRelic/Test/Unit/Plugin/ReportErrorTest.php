<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Test\Unit\Plugin;

use GraphQL\Error\Error;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ErrorHandler;
use Magento\GraphQlNewRelic\Plugin\ReportError;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportErrorTest extends TestCase
{
    /**
     * @var NewRelicWrapper|MockObject
     */
    private $newRelicWrapper;

    /**
     * @var ReportError
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->newRelicWrapper = $this->createMock(NewRelicWrapper::class);
        $this->plugin = new ReportError($this->newRelicWrapper);
    }

    public function testClientSafeErrorsAreNotReported(): void
    {
        $this->newRelicWrapper->expects($this->never())->method('reportError');

        $this->handle([
            new Error('Not found', previous: new GraphQlNoSuchEntityException(__('Could not find a cart'))),
            new Error('Unauthorized', previous: new GraphQlAuthorizationException(__('Not authorized'))),
            new Error('Invalid input', previous: new GraphQlInputException(__('Invalid input'))),
        ]);
    }

    public function testValidationErrorWithoutPreviousIsNotReported(): void
    {
        $this->newRelicWrapper->expects($this->never())->method('reportError');

        $this->handle([new Error('Cannot query field "unknownField" on type "Query".')]);
    }

    public function testServerErrorAfterClientSafeErrorIsReported(): void
    {
        $serverException = new \RuntimeException('Server failure');
        $this->newRelicWrapper->expects($this->once())
            ->method('reportError')
            ->with($this->identicalTo($serverException));

        $this->handle([
            new Error('Not found', previous: new GraphQlNoSuchEntityException(__('Could not find a cart'))),
            new Error('Internal server error', previous: $serverException),
        ]);
    }

    public function testOnlyFirstServerErrorIsReported(): void
    {
        $firstException = new \RuntimeException('First failure');
        $this->newRelicWrapper->expects($this->once())
            ->method('reportError')
            ->with($this->identicalTo($firstException));

        $this->handle([
            new Error('Internal server error', previous: $firstException),
            new Error('Internal server error', previous: new \LogicException('Second failure')),
        ]);
    }

    public function testClientAwareExceptionMarkedUnsafeIsReported(): void
    {
        $unsafeException = new GraphQlInputException(__('Unsafe input'), null, 0, false);
        $this->newRelicWrapper->expects($this->once())
            ->method('reportError')
            ->with($this->identicalTo($unsafeException));

        $this->handle([new Error('Internal server error', previous: $unsafeException)]);
    }

    /**
     * @param Error[] $errors
     */
    private function handle(array $errors): void
    {
        $this->plugin->beforeHandle($this->createStub(ErrorHandler::class), $errors, fn ($error) => $error);
    }
}
