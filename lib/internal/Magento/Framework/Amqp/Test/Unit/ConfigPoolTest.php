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
use PHPUnit\Framework\TestCase;

class ConfigPoolTest extends TestCase
{
    public function testGetConnection()
    {
        $factory = $this->createMock(ConfigFactory::class);
        $config = $this->createMock(Config::class);
        $factory->expects($this->once())->method('create')->with(['connectionName' => 'amqp'])->willReturn($config);
        $model = new ConfigPool($factory);
        $this->assertEquals($config, $model->get('amqp'));
        //test that object is cached
        $this->assertEquals($config, $model->get('amqp'));
    }
}
