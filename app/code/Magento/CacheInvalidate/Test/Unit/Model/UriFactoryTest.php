<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class UriFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $objectManagerMock */
        $objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Zend\Uri\Uri')
            ->willReturn(new \Zend\Uri\Uri());
        $factory = new \Magento\CacheInvalidate\Model\UriFactory($objectManagerMock);
        $this->assertInstanceOf('\Zend\Uri\Uri', $factory->create());
    }
}
