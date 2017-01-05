<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Topology\BindingInstallerType;

use Magento\Framework\Amqp\Topology\BindingInstallerType\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue
     */
    private $model;

    protected function setUp()
    {
        $this->model = new Queue();
    }

    public function testInstall()
    {
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);
        $binding = $this->getMock(BindingInterface::class);
        $binding->expects($this->once())->method('getDestination')->willReturn('queue01');
        $binding->expects($this->once())->method('getTopic')->willReturn('topic01');
        $binding->expects($this->once())->method('getArguments')->willReturn(['some' => 'value']);

        $channel->expects($this->once())
            ->method('queue_bind')
            ->with(
                'queue01',
                'magento',
                'topic01',
                false,
                ['some' => ['S', 'value']],
                null
            );
        $this->model->install($channel, $binding, 'magento');
    }
}
