<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Test\Unit\Model;

use Laminas\Http\Client\Adapter\Socket;
use Magento\CacheInvalidate\Model\SocketFactory;
use PHPUnit\Framework\TestCase;

class SocketFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new SocketFactory();
        $this->assertInstanceOf(Socket::class, $factory->create());
    }
}
