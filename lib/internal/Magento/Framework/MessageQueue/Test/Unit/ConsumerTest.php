<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\Consumer;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
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
     * @var ConsumerConfigurationInterface|MockObject
     */
    private $configuration;

    /**
     * @var MessageEncoder|MockObject
     */
    private $messageEncoder;

    /**
     * @var QueueRepository|MockObject
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @var ConfigInterface|MockObject
     */
    private $consumerConfig;

    /**
     * @var MessageController|MockObject
     */
    private $messageController;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|MockObject
     */
    private $communicationConfig;

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
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configuration = $this
            ->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageEncoder = $this->getMockBuilder(MessageEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueRepository = $this->getMockBuilder(QueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);

        $objectManager = new ObjectManager($this);
        $this->poisonPillCompare = $this->getMockBuilder(PoisonPillCompareInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->poisonPillRead = $this->getMockBuilder(PoisonPillReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        //Hard dependency used because CallbackInvoker invokes closure logic defined inside of Customer class.
        $this->callbackInvoker = new CallbackInvoker(
            $this->poisonPillRead,
            $this->poisonPillCompare,
            $this->deploymentConfig
        );
        $this->consumer = $objectManager->getObject(
            Consumer::class,
            [
                'configuration' => $this->configuration,
                'messageEncoder' => $this->messageEncoder,
                'queueRepository' => $this->queueRepository,
                'invoker' => $this->callbackInvoker,
                'resource' => $this->resource,
                'logger' => $this->logger
            ]
        );

        $this->consumerConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'consumerConfig',
            $this->consumerConfig
        );
        $this->messageController = $this->getMockBuilder(MessageController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'messageController',
            $this->messageController
        );
        $this->communicationConfig = $this
            ->createMock(\Magento\Framework\Communication\ConfigInterface::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'communicationConfig',
            $this->communicationConfig
        );
    }

    /**
     * Test for process method with NotFoundException.
     *
     * @return void
     */
    public function testProcessWithNotFoundException()
    {
        $properties = ['topic_name' => 'topic.name'];
        $topicConfig = [];
        $numberOfMessages = 1;
        $consumerName = 'consumer.name';
        $exceptionPhrase = new Phrase('Exception successfully thrown');
        $this->poisonPillRead->expects($this->atLeastOnce())->method('getLatestVersion')->willReturn('version-1');
        $this->poisonPillCompare->expects($this->atLeastOnce())->method('isLatestVersion')->willReturn(true);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configuration->expects($this->once())->method('getQueue')->willReturn($queue);
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queue->expects($this->atLeastOnce())->method('dequeue')->willReturn($envelope);
        $envelope->expects($this->once())->method('getProperties')->willReturn($properties);
        $this->communicationConfig->expects($this->once())->method('getTopic')->with($properties['topic_name'])
            ->willReturn($topicConfig);
        $this->configuration->expects($this->once())->method('getConsumerName')->willReturn($consumerName);
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
