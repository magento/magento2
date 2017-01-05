<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use PhpAmqpLib\Channel\AMQPChannel;

class QueueInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstall()
    {
        $bindingInstaller = $this->getMock(QueueConfigItemInterface::class);
        $model = new QueueInstaller($bindingInstaller);
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);

        $queue = $this->getMock(QueueConfigItemInterface::class);
        $queue->expects($this->once())->method('getName')->willReturn('queue01');
        $queue->expects($this->once())->method('isDurable')->willReturn(true);
        $queue->expects($this->once())->method('isAutoDelete')->willReturn(false);
        $queue->expects($this->once())->method('getArguments')->willReturn(['some' => 'value']);

        $channel->expects($this->once())
            ->method('queue_declare')
            ->with('queue01', false, true, false, false, false, ['some' => ['S', 'value']], null);
        $model->install($channel, $queue);
    }
}
