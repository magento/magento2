<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class SocketFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $objectManagerMock */
        $objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Zend\Http\Client\Adapter\Socket')
            ->willReturn(new \Zend\Http\Client\Adapter\Socket());
        $factory = new \Magento\CacheInvalidate\Model\SocketFactory($objectManagerMock);
        $this->assertInstanceOf('\Zend\Http\Client\Adapter\Socket', $factory->create());
    }
}
