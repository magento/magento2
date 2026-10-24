<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Stomp\ConnectionTypeResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConnectionTypeResolverTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testGetConnectionType()
    {
        $config = $this->createMock(DeploymentConfig::class);
        $config->expects($this->once())
            ->method('getConfigData')
            ->with('queue')
            ->willReturn(
                [
                    'stomp' => [
                        'host' => '127.0.01',
                        'port' => '61613',
                        'user' => 'artemis',
                        'password' => 'artemis',
                        'ssl' => '',
                        'randomKey' => 'randomValue',
                    ],
                    'connections' => [
                        'connection-01' => [
                            'host' => 'host',
                            'port' => '1515',
                            'user' => 'guest',
                            'password' => 'guest',
                            'ssl' => '',
                            'randomKey' => 'randomValue',
                        ]
                    ]
                ]
            );

        $model = new ConnectionTypeResolver($config);
        $this->assertEquals('stomp', $model->getConnectionType('connection-01'));
        $this->assertEquals('stomp', $model->getConnectionType('stomp'));
    }
}
