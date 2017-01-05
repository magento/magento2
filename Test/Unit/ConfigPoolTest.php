<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit;

class ConfigPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConnection()
    {
        $factory = $this->getMock(\Magento\Framework\Amqp\ConfigFactory::class, [], [], '', false, false);
        $config = $this->getMock(\Magento\Framework\Amqp\Config::class, [], [], '', false, false);
        $factory->expects($this->once())->method('create')->with(['connectionName' => 'amqp'])->willReturn($config);
        $model = new \Magento\Framework\Amqp\ConfigPool($factory);
        $this->assertEquals($config, $model->get('amqp'));
        //test that object is cached
        $this->assertEquals($config, $model->get('amqp'));
    }
}
