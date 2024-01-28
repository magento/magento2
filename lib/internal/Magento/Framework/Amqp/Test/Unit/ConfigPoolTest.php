<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config;
use Magento\Framework\Amqp\ConfigFactory;
use Magento\Framework\Amqp\ConfigPool;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigPoolTest extends TestCase
{
    /**
     * @var ConfigFactory|MockObject
     */
    private $factory;

    /**
     * @var ConfigPool
     */
    private $model;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(ConfigFactory::class);
        $this->model = new ConfigPool($this->factory);
    }

    public function testGetConnection()
    {
        $config = $this->createMock(Config::class);
        $this->factory->expects($this->once())
            ->method('create')
            ->with(['connectionName' => 'amqp'])
            ->willReturn($config);
        $this->assertEquals($config, $this->model->get('amqp'));
        //test that object is cached
        $this->assertEquals($config, $this->model->get('amqp'));
    }

    public function testCloseConnections(): void
    {
        $config = $this->createMock(Config::class);
        $this->factory->method('create')
            ->willReturn($config);
        $this->model->get('amqp');

        $channel = $this->createMock(AMQPChannel::class);
        $config->expects($this->atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);
        $connection = $this->createMock(AbstractConnection::class);
        $channel->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($connection);
        $channel->expects($this->once())
            ->method('close');
        $connection->expects($this->once())
            ->method('close');

        $this->model->closeConnections();
    }
}
