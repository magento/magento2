<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Phrase;

/**
 * Unit test for Consumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configuration;

    /**
     * @var \Magento\Framework\MessageQueue\MessageEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\MessageQueue\QueueRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumerConfig;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageController;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $communicationConfig;

    /**
     * @var \Magento\Framework\MessageQueue\Consumer
     */
    private $consumer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        //Hard dependency used because CallbackInvoker invokes closure logic defined inside of Customer class.
        $this->callbackInvoker = new \Magento\Framework\MessageQueue\CallbackInvoker();
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
