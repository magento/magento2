<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Framework\Phrase;

/**
 * Unit test for Consumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configuration;

    /**
     * @var \Magento\Framework\MessageQueue\MessageEncoder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\MessageQueue\QueueRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $consumerConfig;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageController;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resource;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $communicationConfig;

    /**
     * @var \Magento\Framework\MessageQueue\Consumer
     */
    private $consumer;

    /**
     * @var PoisonPillReadInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $poisonPillRead;

    /**
     * @var PoisonPillCompareInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $poisonPillCompare;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
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
            ->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageEncoder = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageEncoder::class)
            ->disableOriginalConstructor()->getMock();
        $this->queueRepository = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->poisonPillCompare = $this->getMockBuilder(PoisonPillCompareInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->poisonPillRead = $this->getMockBuilder(PoisonPillReadInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        //Hard dependency used because CallbackInvoker invokes closure logic defined inside of Customer class.
        $this->callbackInvoker = new \Magento\Framework\MessageQueue\CallbackInvoker(
            $this->poisonPillRead,
            $this->poisonPillCompare,
            $this->deploymentConfig
        );
        $this->consumer = $objectManager->getObject(
            \Magento\Framework\MessageQueue\Consumer::class,
            [
                'configuration' => $this->configuration,
                'messageEncoder' => $this->messageEncoder,
                'queueRepository' => $this->queueRepository,
                'invoker' => $this->callbackInvoker,
                'resource' => $this->resource,
                'logger' => $this->logger
            ]
        );

        $this->consumerConfig = $this->getMockBuilder(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'consumerConfig',
            $this->consumerConfig
        );
        $this->messageController = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageController::class)
            ->disableOriginalConstructor()->getMock();
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
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->configuration->expects($this->once())->method('getQueue')->willReturn($queue);
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $queue->expects($this->atLeastOnce())->method('dequeue')->willReturn($envelope);
        $envelope->expects($this->once())->method('getProperties')->willReturn($properties);
        $this->communicationConfig->expects($this->once())->method('getTopic')->with($properties['topic_name'])
            ->willReturn($topicConfig);
        $this->configuration->expects($this->once())->method('getConsumerName')->willReturn($consumerName);
        $this->messageController->expects($this->once())->method('lock')->with($envelope, $consumerName)
            ->willThrowException(
                new \Magento\Framework\Exception\NotFoundException(
                    $exceptionPhrase
                )
            );
        $queue->expects($this->once())->method('acknowledge')->with($envelope);
        $this->logger->expects($this->once())->method('warning')->with($exceptionPhrase->render());

        $this->consumer->process($numberOfMessages);
    }
}
