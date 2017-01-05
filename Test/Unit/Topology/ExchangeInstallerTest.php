<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\ExchangeInstaller;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

class ExchangeInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstall()
    {
        $bindingInstaller = $this->getMock(BindingInstallerInterface::class);
        $model = new ExchangeInstaller($bindingInstaller);
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);

        $binding = $this->getMock(BindingInterface::class);

        $exchange = $this->getMock(ExchangeConfigItemInterface::class);
        $exchange->expects($this->exactly(2))->method('getName')->willReturn('magento');
        $exchange->expects($this->once())->method('getType')->willReturn('topic');
        $exchange->expects($this->once())->method('isDurable')->willReturn(true);
        $exchange->expects($this->once())->method('isAutoDelete')->willReturn(false);
        $exchange->expects($this->once())->method('isInternal')->willReturn(false);
        $exchange->expects($this->once())->method('getArguments')->willReturn(['some' => 'value']);
        $exchange->expects($this->once())->method('getBindings')->willReturn(['bind01' => $binding]);

        $channel->expects($this->once())
            ->method('exchange_declare')
            ->with('magento', 'topic', false, true, false, false, false, ['some' => ['S', 'value']], null);
        $bindingInstaller->expects($this->once())->method('install')->with($channel, $binding, 'magento');
        $model->install($channel, $exchange);
    }
}
