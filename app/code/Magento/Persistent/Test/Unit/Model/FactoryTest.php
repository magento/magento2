<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Model;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Persistent\Model\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_factory = $helper->getObject(
            'Magento\Persistent\Model\Factory',
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = 'SomeModel';

        $classMock = $this->getMock('SomeModel');
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            []
        )->will(
            $this->returnValue($classMock)
        );

        $this->assertEquals($classMock, $this->_factory->create($className));
    }

    public function testCreateWithArguments()
    {
        $className = 'SomeModel';
        $data = ['param1', 'param2'];

        $classMock = $this->getMock('SomeModel');
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            $data
        )->will(
            $this->returnValue($classMock)
        );

        $this->assertEquals($classMock, $this->_factory->create($className, $data));
    }
}
