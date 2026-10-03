<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Model\MassConsumer;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallback;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallbackFactory;
use Magento\Framework\MessageQueue\CallbackInvokerInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageStateResetter;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class MassConsumerTest extends TestCase
{
    /**
     * @var CallbackInvokerInterface&MockObject
     */
    private $invoker;

    /**
     * @var QueueInterface&Stub
     */
    private $queue;

    /**
     * @var MassConsumerEnvelopeCallback&MockObject
     */
    private $envelopeCallback;

    /**
     * @var MessageStateResetter&MockObject
     */
    private $messageStateResetter;

    /**
     * @var MassConsumer
     */
    private MassConsumer $massConsumer;

    protected function setUp(): void
    {
        $this->invoker = $this->createMock(CallbackInvokerInterface::class);
        $this->queue = $this->createStub(QueueInterface::class);
        $configuration = $this->createStub(ConsumerConfigurationInterface::class);
        $configuration->method('getQueue')->willReturn($this->queue);
        $this->envelopeCallback = $this->createMock(MassConsumerEnvelopeCallback::class);
        $envelopeCallbackFactory = $this->createStub(MassConsumerEnvelopeCallbackFactory::class);
        $envelopeCallbackFactory->method('create')->willReturn($this->envelopeCallback);
        $this->messageStateResetter = $this->createMock(MessageStateResetter::class);

        $this->massConsumer = new MassConsumer(
            $this->invoker,
            $configuration,
            $envelopeCallbackFactory,
            $this->createStub(Registry::class),
            $this->createStub(ConsumerConfig::class),
            $this->messageStateResetter
        );
    }

    public function testProcessResetsStateAfterEachInvokedMessage(): void
    {
        $envelope = $this->createStub(EnvelopeInterface::class);
        $this->invoker->expects($this->once())->method('invoke')
            ->willReturnCallback(
                static function ($queue, $maxMessages, \Closure $callback) use ($envelope): void {
                    $callback($envelope);
                    $callback($envelope);
                }
            );
        $this->envelopeCallback->expects($this->exactly(2))->method('execute')->with($envelope);

        $this->messageStateResetter->expects($this->exactly(2))->method('resetState');

        $this->massConsumer->process(2);
    }

    public function testProcessResetsStateAfterSubscribedMessageEvenWhenCallbackFails(): void
    {
        $envelope = $this->createStub(EnvelopeInterface::class);
        $this->queue->method('subscribe')
            ->willReturnCallback(static function (\Closure $callback) use ($envelope): void {
                $callback($envelope);
            });
        $this->invoker->expects($this->never())->method('invoke');
        $this->envelopeCallback->expects($this->once())->method('execute')
            ->willThrowException(new \RuntimeException('Failed'));

        $this->messageStateResetter->expects($this->once())->method('resetState');
        $this->expectException(\RuntimeException::class);

        $this->massConsumer->process();
    }
}
