<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Test\Unit\Model;

use Magento\Amqp\Model\Topology;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Amqp\Model\Topology\ExchangeInstaller;
use Magento\Amqp\Model\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfig;
use Magento\Amqp\Model\Config as AmqpConfig;
use PhpAmqpLib\Channel\AMQPChannel;

class TopologyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Topology
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $topologyConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $exchangeInstaller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queueInstaller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $amqpConfig;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->topologyConfig = $this->getMock(TopologyConfig::class);
        $this->exchangeInstaller = $this->getMock(ExchangeInstaller::class, [], [], '', false, false);
        $this->queueInstaller = $this->getMock(QueueInstaller::class, [], [], '', false, false);
        $this->amqpConfig = $this->getMock(AmqpConfig::class, [], [], '', false, false);
        $this->model = $this->objectManager->getObject(
            Topology::class,
            [
                'topologyConfig' => $this->topologyConfig,
                'exchangeInstaller' => $this->exchangeInstaller,
                'queueInstaller' => $this->queueInstaller,
                'amqpConfig' => $this->amqpConfig,
            ]
        );
    }

    public function testInstall()
    {
        $queue = $this->getMock(QueueConfigItemInterface::class);
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);
        $this->amqpConfig->expects($this->any())->method('getChannel')->willReturn($channel);

        $this->topologyConfig->expects($this->once())->method('getQueues')->willReturn([$queue]);
        $this->queueInstaller->expects($this->once())->method('install')->with($channel, $queue);

        $exchange = $this->getMock(ExchangeConfigItemInterface::class);
        $exchange->expects($this->once())->method('getConnection')->willReturn('amqp');
        $this->topologyConfig->expects($this->once())->method('getExchanges')->willReturn([$exchange]);
        $this->exchangeInstaller->expects($this->once())->method('install')->with($channel, $exchange);

        $this->model->install();
    }

    public function testInstallWithNotAmqpConnection()
    {
        $queue = $this->getMock(QueueConfigItemInterface::class);
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);
        $this->amqpConfig->expects($this->any())->method('getChannel')->willReturn($channel);

        $this->topologyConfig->expects($this->once())->method('getQueues')->willReturn([$queue]);
        $this->queueInstaller->expects($this->once())->method('install')->with($channel, $queue);

        $exchange = $this->getMock(ExchangeConfigItemInterface::class);
        $exchange->expects($this->once())->method('getConnection')->willReturn('db');
        $this->topologyConfig->expects($this->once())->method('getExchanges')->willReturn([$exchange]);
        $this->exchangeInstaller->expects($this->never())->method('install');

        $this->model->install();
    }
}
