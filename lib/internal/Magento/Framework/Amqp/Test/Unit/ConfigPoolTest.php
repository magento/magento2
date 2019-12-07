<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit;

class ConfigPoolTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConnection()
    {
        $factory = $this->createMock(\Magento\Framework\Amqp\ConfigFactory::class);
        $config = $this->createMock(\Magento\Framework\Amqp\Config::class);
        $factory->expects($this->once())->method('create')->with(['connectionName' => 'amqp'])->willReturn($config);
        $model = new \Magento\Framework\Amqp\ConfigPool($factory);
        $this->assertEquals($config, $model->get('amqp'));
        //test that object is cached
        $this->assertEquals($config, $model->get('amqp'));
    }
}
