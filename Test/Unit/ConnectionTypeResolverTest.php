<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\ConnectionTypeResolver;
use Magento\Framework\App\DeploymentConfig;

class ConnectionTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConnectionType()
    {
        $config = $this->getMock(DeploymentConfig::class, [], [], '', false, false);
        $config->expects($this->once())
            ->method('getConfigData')
            ->with('queue')
            ->will($this->returnValue(
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
            ));

        $model = new ConnectionTypeResolver($config);
        $this->assertEquals('amqp', $model->getConnectionType('connection-01'));
        $this->assertEquals('amqp', $model->getConnectionType('amqp'));
    }
}
