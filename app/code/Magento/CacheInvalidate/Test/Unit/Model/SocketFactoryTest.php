<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class SocketFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $factory = new \Magento\CacheInvalidate\Model\SocketFactory();
        $this->assertInstanceOf('\Zend\Http\Client\Adapter\Socket', $factory->create());
    }
}
