<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class SocketFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $factory = new \Magento\CacheInvalidate\Model\SocketFactory();
        $this->assertInstanceOf(\Zend\Http\Client\Adapter\Socket::class, $factory->create());
    }
}
