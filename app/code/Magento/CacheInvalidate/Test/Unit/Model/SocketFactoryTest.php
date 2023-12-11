<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
