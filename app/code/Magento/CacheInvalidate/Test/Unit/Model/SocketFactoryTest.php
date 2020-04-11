<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\CacheInvalidate\Model\SocketFactory;
use Laminas\Http\Client\Adapter\Socket;

class SocketFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new SocketFactory();
        $this->assertInstanceOf(Socket::class, $factory->create());
    }
}
