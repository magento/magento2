<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\Consumer;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface as UsedConsumerConfig;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for Consumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var CommunicationConfig|MockObject
     */
    private $communicationConfig;

    /**
     * @var CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @var ConsumerConfig|MockObject
     */
    private $consumerConfig;

    /**
     * @var UsedConsumerConfig|MockObject
     */
    private $usedConsumerConfig;

    /**
     * @var MessageController|MockObject
     */
    private $messageController;

    /**
     * @var MessageEncoder|MockObject
     */
    private $messageEncoder;

    /**
     * @var QueueRepository|MockObject
     */
    private $queueRepository;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var PoisonPillReadInterface|MockObject
     */
    private $poisonPillRead;

    /**
     * @var PoisonPillCompareInterface|MockObject
     */
    private $poisonPillCompare;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->communicationConfig = $this->createMock(CommunicationConfig::class);

        //Hard dependency used because CallbackInvoker invokes closure logic defined inside of Customer class.
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->poisonPillCompare = $this->getMockBuilder(PoisonPillCompareInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->poisonPillRead = $this->getMockBuilder(PoisonPillReadInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->callbackInvoker = new CallbackInvoker(
            $this->poisonPillRead,
            $this->poisonPillCompare,
            $this->deploymentConfig
        );
        $this->consumerConfig = $this->getMockBuilder(ConsumerConfig::class)->getMock();
        $this->usedConsumerConfig = $this->getMockBuilder(UsedConsumerConfig::class)->getMock();
        $this->messageController = $this->getMockBuilder(MessageController::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageEncoder = $this->getMockBuilder(MessageEncoder::class)
            ->disableOriginalConstructor()->getMock();
        $this->queueRepository = $this->getMockBuilder(QueueRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $this->consumer = $objectManager->getObject(
            Consumer::class,
            [
                'resource' => $this->resource,
                'communicationConfig' => $this->communicationConfig,
                'invoker' => $this->callbackInvoker,
                'usedConsumerConfig' => $this->usedConsumerConfig,
                'messageController' => $this->messageController,
                'messageEncoder' => $this->messageEncoder,
                'queueRepository' => $this->queueRepository,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * Test for process method with NotFoundException.
     *
     * @return void
     */
    public function testProcessWithNotFoundException(): void
    {
        $properties = ['topic_name' => 'topic.name'];
        $topicConfig = [];
        $numberOfMessages = 1;
        $consumerName = 'consumer.name';
        $exceptionPhrase = new Phrase('Exception successfully thrown');
        $this->poisonPillRead->expects($this->atLeastOnce())->method('getLatestVersion')->willReturn('version-1');
        $this->poisonPillCompare->expects($this->atLeastOnce())->method('isLatestVersion')->willReturn(true);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->usedConsumerConfig->expects($this->once())->method('getQueue')->willReturn($queue);
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $queue->expects($this->atLeastOnce())->method('dequeue')->willReturn($envelope);
        $envelope->expects($this->once())->method('getProperties')->willReturn($properties);
        $this->communicationConfig->expects($this->once())->method('getTopic')->with($properties['topic_name'])
            ->willReturn($topicConfig);
        $this->usedConsumerConfig->expects($this->once())->method('getConsumerName')->willReturn($consumerName);
        $this->messageController->expects($this->once())->method('lock')->with($envelope, $consumerName)
            ->willThrowException(
                new NotFoundException(
                    $exceptionPhrase
                )
            );
        $queue->expects($this->once())->method('acknowledge')->with($envelope);
        $this->logger->expects($this->once())->method('warning')->with($exceptionPhrase->render());

        $this->consumer->process($numberOfMessages);
    }
}
