<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\ConnectionTypeResolver;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\TestCase;

class ConnectionTypeResolverTest extends TestCase
{
    public function testGetConnectionType()
    {
        $config = $this->createMock(DeploymentConfig::class);
        $config->expects($this->once())
            ->method('getConfigData')
            ->with('queue')
            ->willReturn(
                [
                    'amqp' => [
                        'host' => '127.0.01',
                        'port' => '8989',
                        'user' => 'admin',
                        'password' => 'admin',
                        'virtualhost' => 'root',
                        'ssl' => '',
                        'randomKey' => 'randomValue',
                    ],
                    'connections' => [
                        'connection-01' => [
                            'host' => 'host',
                            'port' => '1515',
                            'user' => 'guest',
                            'password' => 'guest',
                            'virtualhost' => 'localhost',
                            'ssl' => '',
                            'randomKey' => 'randomValue',
                        ]
                    ]
                ]
            );

        $model = new ConnectionTypeResolver($config);
        $this->assertEquals('amqp', $model->getConnectionType('connection-01'));
        $this->assertEquals('amqp', $model->getConnectionType('amqp'));
    }
}
