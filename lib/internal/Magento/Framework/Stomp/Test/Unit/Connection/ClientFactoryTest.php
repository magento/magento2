<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit\Connection;

use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\Connection\ClientFactory;
use Magento\Framework\Stomp\Connection\Factory;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    public function testCreateUsesIndependentConnectionsAndParsers(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->never())->method('getConnection');
        $config->method('getValue')->willReturnMap([
            [Config::HOST, 'localhost'],
            [Config::PORT, '61613'],
            [Config::USERNAME, 'test-user'],
            [Config::PASSWORD, 'test-password'],
        ]);
        $factory = new ClientFactory(new Factory());
        $first = $factory->create($config);
        $second = $factory->create($config);

        self::assertNotSame($first->getConnection(), $second->getConnection());
        self::assertNotSame($first->getConnection()->getParser(), $second->getConnection()->getParser());
        self::assertNotSame($first->getConnection()->getObservers(), $second->getConnection()->getObservers());
        self::assertFalse($first->isConnected());
        self::assertFalse($second->isConnected());
    }
}
