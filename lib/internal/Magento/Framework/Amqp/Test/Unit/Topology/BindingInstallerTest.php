<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\BindingInstaller;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

class BindingInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testInstall()
    {
        $installerOne = $this->getMockForAbstractClass(BindingInstallerInterface::class);
        $installerTwo = $this->getMockForAbstractClass(BindingInstallerInterface::class);
        $model = new BindingInstaller(
            [
                'queue' => $installerOne,
                'exchange' => $installerTwo,
            ]
        );
        $channel = $this->createMock(AMQPChannel::class);
        $binding = $this->getMockForAbstractClass(BindingInterface::class);
        $binding->expects($this->once())->method('getDestinationType')->willReturn('queue');
        $installerOne->expects($this->once())->method('install')->with($channel, $binding, 'magento');
        $installerTwo->expects($this->never())->method('install');
        $model->install($channel, $binding, 'magento');
    }

    /**
     */
    public function testInstallInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Installer type [test] is not configured');

        $installerOne = $this->getMockForAbstractClass(BindingInstallerInterface::class);
        $installerTwo = $this->getMockForAbstractClass(BindingInstallerInterface::class);
        $model = new BindingInstaller(
            [
                'queue' => $installerOne,
                'exchange' => $installerTwo,
            ]
        );
        $channel = $this->createMock(AMQPChannel::class);
        $binding = $this->getMockForAbstractClass(BindingInterface::class);
        $binding->expects($this->once())->method('getDestinationType')->willReturn('test');
        $installerOne->expects($this->never())->method('install');
        $installerTwo->expects($this->never())->method('install');
        $model->install($channel, $binding, 'magento');
    }
}
