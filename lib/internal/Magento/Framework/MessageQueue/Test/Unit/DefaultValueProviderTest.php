<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use PHPUnit\Framework\TestCase;

class DefaultValueProviderTest extends TestCase
{
    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @var DefaultValueProvider
     */
    private DefaultValueProvider $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new DefaultValueProvider('db', 'magento', $this->config);
    }

    /**
     * @return void
     */
    public function testGetConnection(): void
    {
        $this->config->method('get')->willReturnMap(
            [
                ['queue', null, ['key' => 'test_connection']],
            ]
        );
        $this->assertEquals('db', $this->model->getConnection());
    }

    /**
     * @return void
     *
     */
    public function testGetDefaultConnection(): void
    {
        $this->config->method('get')->willReturnMap(
            [
                ['queue/default_connection', null, 'test_connection'],
            ]
        );

        $this->assertEquals('test_connection', $this->model->getConnection());
    }

    /**
     * @return void
     *
     */
    public function testGetAMQPConnection(): void
    {
        $this->config->method('get')->willReturnMap(
            [
                ['queue/default_connection', null, null],
                ['queue/amqp', null, ['host' => '127.0.0.1', 'port' => '5672']],
                ['queue', null, ['amqp' => ['host' => '127.0.0.1', 'port' => '5672']]]
            ]
        );

        $this->assertEquals('amqp', $this->model->getConnection());
    }
}
