<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\MessageStateResetter;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MessageStateResetterTest extends TestCase
{
    public function testResetStateResetsEveryRegisteredService(): void
    {
        $first = $this->createMock(ResetAfterRequestInterface::class);
        $second = $this->createMock(ResetAfterRequestInterface::class);
        $first->expects($this->once())->method('_resetState');
        $second->expects($this->once())->method('_resetState');

        $this->createResetter(['first' => $first, 'second' => $second, 'other' => new \stdClass()])
            ->resetState($this->createConfiguration([]));
    }

    public function testResetStateResetsHandlerInstancesOnce(): void
    {
        $sharedHandler = $this->createMock(ResetAfterRequestInterface::class);
        $topicHandler = $this->createMock(ResetAfterRequestInterface::class);
        $sharedHandler->expects($this->once())->method('_resetState');
        $topicHandler->expects($this->once())->method('_resetState');

        $this->createResetter([$sharedHandler])->resetState(
            $this->createConfiguration(
                [
                    'topic.one' => [[$sharedHandler, 'execute'], [new \stdClass(), 'execute']],
                    'topic.two' => [[$topicHandler, 'execute'], [$sharedHandler, 'execute'], 'strlen'],
                ]
            )
        );
    }

    public function testResetStateReadsConsumerHandlersOncePerConfiguration(): void
    {
        $handler = $this->createMock(ResetAfterRequestInterface::class);
        $configuration = $this->createMock(ConsumerConfigurationInterface::class);
        $configuration->expects($this->once())->method('getTopicNames')->willReturn(['topic.one']);
        $configuration->expects($this->once())->method('getHandlers')->with('topic.one')
            ->willReturn([[$handler, 'execute']]);

        $handler->expects($this->exactly(2))->method('_resetState');

        $resetter = $this->createResetter([]);
        $resetter->resetState($configuration);
        $resetter->resetState($configuration);
    }

    public function testResetStateLogsFailingResetAndResetsTheRest(): void
    {
        $exception = new \RuntimeException('Reset failed');
        $failing = $this->createMock(ResetAfterRequestInterface::class);
        $failing->expects($this->once())->method('_resetState')->willThrowException($exception);
        $next = $this->createMock(ResetAfterRequestInterface::class);
        $next->expects($this->once())->method('_resetState');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')
            ->with($this->stringContains('Could not reset state'), ['exception' => $exception]);

        (new MessageStateResetter([$failing, $next], $logger))->resetState($this->createConfiguration([]));
    }

    private function createResetter(array $services): MessageStateResetter
    {
        return new MessageStateResetter($services, $this->createStub(LoggerInterface::class));
    }

    private function createConfiguration(array $handlersByTopic): ConsumerConfigurationInterface
    {
        $configuration = $this->createStub(ConsumerConfigurationInterface::class);
        $configuration->method('getTopicNames')->willReturn(array_keys($handlersByTopic));
        $configuration->method('getHandlers')
            ->willReturnCallback(static fn (string $topicName) => $handlersByTopic[$topicName]);
        return $configuration;
    }
}
