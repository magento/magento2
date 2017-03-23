<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Reader;

use \Magento\Framework\ObjectManager\Config\Reader\DomFactory;

class DomFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomFactory
     */
    protected $_factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_object = $this->getMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class, [], [], '', false);
        $this->_objectManager = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_factory = new DomFactory($this->_objectManager);
    }

    public function testCreate()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\ObjectManager\Config\Reader\Dom::class)
            ->will($this->returnValue($this->_object));

        $this->_factory->create([1]);
    }
}
