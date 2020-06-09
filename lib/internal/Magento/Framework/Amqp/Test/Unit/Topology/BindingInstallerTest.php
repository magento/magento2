<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\BindingInstaller;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;

class BindingInstallerTest extends TestCase
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

    public function testInstallInvalidType()
    {
        $this->expectException('InvalidArgumentException');
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
