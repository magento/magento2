<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\ObjectManager\Config\Reader;

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

    public function setUp()
    {
        $this->_object = $this->getMock('Magento\Framework\ObjectManager\Config\Reader\Dom', [], [], '', false);
        $this->_objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
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
            ->with('Magento\Framework\ObjectManager\Config\Reader\Dom')
            ->will($this->returnValue($this->_object));

        $this->_factory->create([1]);
    }
}
