<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Reader;

use \Magento\Framework\ObjectManager\Config\Reader\DomFactory;

class DomFactoryTest extends \PHPUnit\Framework\TestCase
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
        $this->_object = $this->createMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class);
        $this->_objectManager =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
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
