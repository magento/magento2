<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Event\Test\Unit;

use \Magento\Framework\Event\Config;

use Magento\Framework\Event\Config\Data;

/**
 * Class ConfigTest
 *
 * @package Magento\Framework\Event
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataContainerMock;

    /**
     * @var Config
     */
    protected $config;

    protected function setUp()
    {
        $this->dataContainerMock = $this->createPartialMock(\Magento\Framework\Event\Config\Data::class, ['get']);
        $this->config = new Config($this->dataContainerMock);
    }

    public function testGetObservers()
    {
        $eventName = 'some_event';
        $observers = ['observer1', 'observer3'];
        $this->dataContainerMock->expects($this->once())
            ->method('get')
            ->with($eventName, $this->equalTo([]))
            ->will($this->returnValue($observers));

        $result = $this->config->getObservers($eventName);
        $this->assertEquals($observers, $result);
    }
}
