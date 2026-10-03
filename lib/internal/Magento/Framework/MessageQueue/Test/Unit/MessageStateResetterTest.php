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

class MessageStateResetterTest extends TestCase
{
    public function testResetStateResetsEveryRegisteredService(): void
    {
        $first = $this->createMock(ResetAfterRequestInterface::class);
        $second = $this->createMock(ResetAfterRequestInterface::class);
        $first->expects($this->once())->method('_resetState');
        $second->expects($this->once())->method('_resetState');

        (new MessageStateResetter(['first' => $first, 'second' => $second, 'other' => new \stdClass()]))
            ->resetState($this->createConfiguration([]));
    }

    public function testResetStateResetsHandlerInstancesOnce(): void
    {
        $sharedHandler = $this->createMock(ResetAfterRequestInterface::class);
        $topicHandler = $this->createMock(ResetAfterRequestInterface::class);
        $sharedHandler->expects($this->once())->method('_resetState');
        $topicHandler->expects($this->once())->method('_resetState');

        (new MessageStateResetter([$sharedHandler]))->resetState(
            $this->createConfiguration(
                [
                    'topic.one' => [[$sharedHandler, 'execute'], [new \stdClass(), 'execute']],
                    'topic.two' => [[$topicHandler, 'execute'], [$sharedHandler, 'execute'], 'strlen'],
                ]
            )
        );
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
