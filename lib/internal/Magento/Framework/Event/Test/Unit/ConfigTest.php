<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event\Config;
use Magento\Framework\Event\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $dataContainerMock;

    /**
     * @var Config
     */
    protected $config;

    protected function setUp(): void
    {
        $this->dataContainerMock = $this->createPartialMock(Data::class, ['get']);
        $this->config = new Config($this->dataContainerMock);
    }

    public function testGetObservers()
    {
        $eventName = 'some_event';
        $observers = ['observer1', 'observer3'];
        $this->dataContainerMock->expects($this->once())
            ->method('get')
            ->with($eventName, [])
            ->willReturn($observers);

        $result = $this->config->getObservers($eventName);
        $this->assertEquals($observers, $result);
    }
}
